<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Main\Cli;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Data\UpdateResult;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Fields\FieldTypeMask;
use Bitrix\Main\ORM\Fields\UserTypeField;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Relations\ManyToMany;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Objectify\Collection;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\ScalarField;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package    bitrix
 * @subpackage main
 */
class OrmAnnotateCommand extends Command
{
	protected $debug = 0;

	protected $modulesScanned = [];

	protected $filesIncluded = 0;

	protected $entitiesFound = [];

	protected $excludedFiles = [
		'main/lib/text/string.php',
		'main/lib/composite/compatibility/aliases.php',
	];

	const ANNOTATION_MARKER = 'ORMENTITYANNOTATION';

	protected function configure()
	{
		$inBitrixDir = realpath(Application::getDocumentRoot().Application::getPersonalRoot()) === realpath(getcwd());

		$this
			// the name of the command (the part after "bin/console")
			->setName('orm:annotate')

			// the short description shown while running "php bin/console list"
			->setDescription('Scans project for ORM Entities.')

			// the full command description shown when running the command with
			// the "--help" option
			->setHelp('This system command optimizes Entity Relation Map building.')

			->setDefinition(
				new InputDefinition(array(
					new InputArgument(
						'output', InputArgument::OPTIONAL, 'File for annotations to be saved to',
						$inBitrixDir
							? 'modules/orm_annotations.php'
							: Application::getDocumentRoot().Application::getPersonalRoot().'/modules/orm_annotations.php'
					),
					new InputOption(
						'modules', 'm', InputOption::VALUE_OPTIONAL,
						'Modules to be scanned, separated by comma.', 'main'
					),
					new InputOption(
						'clean', 'c', InputOption::VALUE_NONE,
						'Clean current entity map.'
					),
				))
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln([
			'Entity Scanner',
			'==============',
			'',
		]);

		$time = getmicrotime();
		$memoryBefore = memory_get_usage();

		// handle already known classes (but we don't know their modules)
		// as long as there are no any Table by default, we can ignore it
		$this->handleClasses(get_declared_classes(), $input, $output);

		// scan dirs
		$inputModules = [];
		$inputModulesRaw = $input->getOption('modules');

		if (!empty($inputModulesRaw) && $inputModulesRaw != 'all')
		{
			$inputModules = explode(',', $inputModulesRaw);
		}

		$dirs = $this->getDirsToScan($inputModules, $input, $output);

		foreach ($dirs as $dir)
		{
			$this->scanDir($dir, $input, $output);
		}

		// output file path
		$filePath = $input->getArgument('output');
		$filePath = ($filePath{0} == '/')
			? $filePath // absolute
			: getcwd().'/'.$filePath; // relative

		// handle entities
		$annotations = [];

		// get current annotations
		if (!$input->getOption('clean') && file_exists($filePath) && is_readable($filePath))
		{
			$rawAnnotations = explode('/* '.static::ANNOTATION_MARKER, file_get_contents($filePath));

			foreach ($rawAnnotations as $rawAnnotation)
			{
				if ($rawAnnotation{0} === ':')
				{
					$endPos = strpos($rawAnnotation, ' */');
					$entityClass = substr($rawAnnotation, 1, $endPos-1);
					//$annotation = substr($rawAnnotation, $endPos + 3 + strlen(PHP_EOL));

					$annotations[$entityClass] = '/* '.static::ANNOTATION_MARKER.rtrim($rawAnnotation);
				}
			}
		}

		// add/rewrite new entities
		foreach ($this->entitiesFound as $entityClass)
		{
			$entity = Entity::getInstance($entityClass);
			$entityAnnotation = static::annotateEntity($entity, $input,$output);
			$annotations[$entityClass] = "/* ".static::ANNOTATION_MARKER.":{$entityClass} */".PHP_EOL;
			$annotations[$entityClass] .= $entityAnnotation;
		}

		// write to file
		$fileContent = '<?php'.PHP_EOL.PHP_EOL.join(PHP_EOL, $annotations);
		file_put_contents($filePath, $fileContent);

		$output->writeln('Map has been saved to: '.$filePath);

		// summary stats
		$time = round(getmicrotime() - $time, 2);
		$memoryAfter = memory_get_usage();
		$memoryDiff = $memoryAfter - $memoryBefore;

		$output->writeln('Scanned modules: '.join(', ', $this->modulesScanned));
		$output->writeln('Scanned files: '.$this->filesIncluded);
		$output->writeln('Found entities: '.count($this->entitiesFound));
		$output->writeln('Time: '.$time.' sec');
		$output->writeln('Memory usage: '.(round($memoryAfter/1024/1024, 1)).'M (+'.(round($memoryDiff/1024/1024, 1)).'M)');
		$output->writeln('Memory peak usage: '.(round(memory_get_peak_usage()/1024/1024, 1)).'M');
	}

	protected function getDirsToScan($inputModules, InputInterface $input, OutputInterface $output)
	{
		$basePath = Application::getDocumentRoot().Application::getPersonalRoot().'/modules/';

		$moduleList = [];
		$dirs = [];

		foreach (new \DirectoryIterator($basePath) as $item)
		{
			if($item->isDir() && !$item->isDot())
			{
				$moduleList[] = $item->getFilename();
			}
		}

		// filter for input modules
		if (!empty($inputModules))
		{
			$moduleList = array_intersect($moduleList, $inputModules);
		}

		foreach ($moduleList as $moduleName)
		{
			// filter for installed modules
			if (!Loader::includeModule($moduleName))
			{
				continue;
			}

			$libDir = $basePath.$moduleName.'/lib';
			if (is_dir($libDir) && is_readable($libDir))
			{
				$dirs[] = $libDir;
			}

			$libDir = $basePath.$moduleName.'/dev/lib';
			if (is_dir($libDir) && is_readable($libDir))
			{
				$dirs[] = $libDir;
			}

			$this->modulesScanned[] = $moduleName;
		}

		return $dirs;
	}

	protected function scanDir($dir, InputInterface $input, OutputInterface $output)
	{
		$this->debug($output,'scan dir: '.$dir);

		foreach (
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS | \RecursiveDirectoryIterator::FOLLOW_SYMLINKS),
				\RecursiveIteratorIterator::SELF_FIRST) as $item
		)
		{
			// check for stop list
			foreach ($this->excludedFiles as $excludedFile)
			{
				$currentPath = str_replace('\\', '/', $item->getPathname());
				if (substr($currentPath, -strlen($excludedFile)) === $excludedFile)
				{
					continue 2;
				}
			}

			/** @var $iterator \RecursiveDirectoryIterator */
			/** @var $item \SplFileInfo */
			if ($item->isFile() && $item->isReadable() && substr($item->getFilename(), -4) == '.php')
			{
				$this->debug($output,'handle file: '.$item->getPathname());

				// get classes from file
				$classes = get_declared_classes();

				try
				{
					include_once $item->getPathname();
					$this->filesIncluded++;
				}
				catch (\Exception $e)
				{
					throw  $e;
				}

				$classes = array_diff(get_declared_classes(), $classes);

				// check classes
				$this->handleClasses($classes, $input, $output);
			}
		}
	}

	protected function handleClasses($classes, InputInterface $input, OutputInterface $output)
	{
		foreach ($classes as $class)
		{
			$debugMsg = $class;

			if (is_subclass_of($class, DataManager::class))
			{
				$debugMsg .= ' found!';
				$this->entitiesFound[] = $class;
			}

			$this->debug($output, $debugMsg);
		}
	}

	public static function annotateEntity(Entity $entity, InputInterface $input, OutputInterface $output)
	{
		$entityNamespace = trim($entity->getNamespace(), '\\');
		$dataClass = $entity->getDataClass();

		$objectClass = $entity->getObjectClass();
		$objectClassName = $entity->getObjectClassName();
		$objectNamespace =  trim(
			substr($objectClass, 0, strrpos($objectClass, '\\')),
			'\\'
		);

		$collectionClass = $entity->getCollectionClass();
		$collectionClassName = $entity->getCollectionClassName();
		$collectionNamespace =  trim(
			substr($collectionClass, 0, strrpos($collectionClass, '\\')),
			'\\'
		);

		$code = [];
		$objectCode = [];
		$collectionCode = [];

		$code[] = "namespace {$objectNamespace} {"; // start namespace
		$code[] = "\t/**"; // start class annotations
		$code[] = "\t * {$objectClassName}";
		$code[] = "\t * @see {$dataClass}";
		$code[] = "\t *";
		$code[] = "\t * Custom methods:";
		$code[] = "\t * ---------------";
		$code[] = "\t *";

		foreach ($entity->getFields() as $field)
		{
			$objectFieldCode = [];
			$collectionFieldCode = [];

			if ($field instanceof ScalarField)
			{
				list($objectFieldCode, $collectionFieldCode) = static::annotateScalarField($field);
			}
			elseif ($field instanceof UserTypeField)
			{
				list($objectFieldCode, $collectionFieldCode) = static::annotateUserType($field);
			}
			elseif ($field instanceof ExpressionField)
			{
				list($objectFieldCode, $collectionFieldCode) = static::annotateExpression($field);
			}
			elseif ($field instanceof Reference)
			{
				list($objectFieldCode, $collectionFieldCode) = static::annotateReference($field);
			}
			elseif ($field instanceof OneToMany)
			{
				list($objectFieldCode, $collectionFieldCode) = static::annotateOneToMany($field);
			}
			elseif ($field instanceof ManyToMany)
			{
				list($objectFieldCode, $collectionFieldCode) = static::annotateManyToMany($field);
			}

			$objectCode = array_merge($objectCode, $objectFieldCode);
			$collectionCode = array_merge($collectionCode, $collectionFieldCode);
		}

		// common class methods
		$code = array_merge($code, $objectCode);
		$code[] = "\t *";
		$code[] = "\t * Common methods:";
		$code[] = "\t * ---------------";
		$code[] = "\t *";
		$code[] = "\t * @method \\".DataManager::class." dataClass()";
		$code[] = "\t * @method \\".Entity::class." entity()";
		$code[] = "\t * @method array primary()";
		$code[] = "\t * @method mixed get(\$fieldName)";
		$code[] = "\t * @method mixed actual(\$fieldName)";
		$code[] = "\t * @method mixed require(\$fieldName)";
		$code[] = "\t * @method {$objectClassName} set(\$fieldName, \$value)";
		$code[] = "\t * @method {$objectClassName} reset(\$fieldName)";
		$code[] = "\t * @method {$objectClassName} unset(\$fieldName)";
		$code[] = "\t * @method void addTo(\$fieldName, \$value)";
		$code[] = "\t * @method void removeFrom(\$fieldName, \$value)";
		$code[] = "\t * @method void removeAll(\$fieldName)";
		$code[] = "\t * @method void delete()";
		$code[] = "\t * @method void fill(\$fields = \\".FieldTypeMask::class."::ALL) flag or array of field names";
		$code[] = "\t * @method mixed[] values()";
		$code[] = "\t * @method \\".AddResult::class."|\\".UpdateResult::class."|bool save()";
		$code[] = "\t * @method static {$objectClassName} wakeUp(\$data)";
		$code[] = "\t *";

		$code[] = "\t * for parent class, @see \\".EntityObject::class;
		// TODO we can put path to the original file here
		$code[] = "\t */"; // end class annotations
		$code[] = "\tclass {$objectClassName} {}";

		// compatibility with default classes
		if (strpos($objectClassName, Entity::DEFAULT_OBJECT_PREFIX) !== 0) // better to compare full classes definitions
		{
			$defaultObjectClassName = Entity::getDefaultObjectClassName($entity->getName());
			$code[] = "\tclass_alias('{$objectClass}', '{$entityNamespace}\\{$defaultObjectClassName}');";
		}

		$code[] = "}"; // end namespace

		// annotate collection class
		$code[] = "namespace {$collectionNamespace} {"; // start namespace
		$code[] = "\t/**";
		$code[] = "\t * {$collectionClassName}";
		$code[] = "\t *";
		$code[] = "\t * Custom methods:";
		$code[] = "\t * ---------------";
		$code[] = "\t *";

		$code = array_merge($code, $collectionCode);

		$code[] = "\t *";
		$code[] = "\t * Common methods:";
		$code[] = "\t * ---------------";
		$code[] = "\t *";
		$code[] = "\t * @method void fill(\$fields = \\".FieldTypeMask::class."::ALL) flag or array of field names";
		$code[] = "\t * @method {$objectClass} current()";
		$code[] = "\t * @method {$objectClass}[] getAll()";
		// TODO we can put path to the original file here
		$code[] = "\t */";
		$code[] = "\tclass {$collectionClassName} extends \\".Collection::class." {}";

		// compatibility with default classes
		if (strpos($objectClassName, Entity::DEFAULT_OBJECT_PREFIX) !== 0) // better to compare full classes definitions
		{
			$defaultCollectionClassName = Entity::getDefaultCollectionClassName($entity->getName());
			$code[] = "\tclass_alias('{$entityNamespace}\\{$collectionClassName}', '{$entityNamespace}\\{$defaultCollectionClassName}');";
		}

		$code[] = "}"; // end namespace


		// annotate query and result
		$dataClassName = $entity->getName().'Table';
		$queryClassName = Entity::DEFAULT_OBJECT_PREFIX.$entity->getName().'Query';
		$resultClassName = Entity::DEFAULT_OBJECT_PREFIX.$entity->getName().'Result';

		$code[] = "namespace {$entityNamespace} {"; // start namespace
		$code[] = "\t/**";
		$code[] = "\t * @method $queryClassName query()";
		$code[] = "\t */";
		$code[] = "\tclass {$dataClassName} {}";

		$code[] = "\t/**";
		$code[] = "\t * @method $resultClassName exec()";
		$code[] = "\t */";
		$code[] = "\tclass {$queryClassName} extends \\".Query::class." {}";

		$code[] = "\t/**";
		$code[] = "\t * @method {$objectClass} fetchObject()";
		$code[] = "\t * @method {$collectionClass} fetchCollection()";
		$code[] = "\t */";
		$code[] = "\tclass {$resultClassName} {}";

		$code[] = "}"; // end namespace

		return join(PHP_EOL, $code);
	}

	public static function annotateScalarField(ScalarField $field)
	{
		// TODO no setter if it is reference-elemental (could expressions become elemental?)

		$objectClassName = $field->getEntity()->getObjectClassName();
		$dataType = static::scalarFieldToTypeHint($field);
		list($lName, $uName) = static::getFieldNameCamelCase($field->getName());

		$objectCode = [];
		$collectionCode = [];

		$objectCode[] = "\t * @method {$dataType} get{$uName}()";
		$objectCode[] = "\t * @method {$objectClassName} set{$uName}({$dataType} \${$lName})";

		$collectionCode[] = "\t * @method {$dataType}[] get{$uName}List()";

		if (!$field->isPrimary())
		{
			$objectCode[] = "\t * @method {$dataType} actual{$uName}()";
			$objectCode[] = "\t * @method {$dataType} require{$uName}()";

			$objectCode[] = "\t * @method {$objectClassName} reset{$uName}()";
			$objectCode[] = "\t * @method {$objectClassName} unset{$uName}()";

			$objectCode[] = "\t * @method {$dataType} fill{$uName}()";
			$collectionCode[] = "\t * @method fill{$uName}()";
		}

		return [$objectCode, $collectionCode];
	}

	public static function annotateUserType(UserTypeField $field)
	{
		// no setter
		$objectClassName = $field->getEntity()->getObjectClassName();
		$dataType = static::scalarFieldToTypeHint($field->getValueType());
		$dataType = $field->isMultiple() ? $dataType.'[]' : $dataType;
		list($lName, $uName) = static::getFieldNameCamelCase($field->getName());

		list($objectCode, $collectionCode) = static::annotateExpression($field);

		// add setter
		$objectCode[] = "\t * @method {$objectClassName} set{$uName}({$dataType} \${$lName})";

		return [$objectCode, $collectionCode];
	}

	public static function annotateExpression(ExpressionField $field)
	{
		// no setter
		$objectClassName = $field->getEntity()->getObjectClassName();
		$dataType = static::scalarFieldToTypeHint($field->getValueType());
		list($lName, $uName) = static::getFieldNameCamelCase($field->getName());

		$objectCode = [];
		$collectionCode = [];

		$objectCode[] = "\t * @method {$dataType} get{$uName}()";
		$objectCode[] = "\t * @method {$dataType} actual{$uName}()";
		$objectCode[] = "\t * @method {$dataType} require{$uName}()";

		$collectionCode[] = "\t * @method {$dataType}[] get{$uName}List()";

		$objectCode[] = "\t * @method {$objectClassName} unset{$uName}()";

		$objectCode[] = "\t * @method {$dataType} fill{$uName}()";
		$collectionCode[] = "\t * @method fill{$uName}()";

		return [$objectCode, $collectionCode];
	}

	public static function annotateReference(Reference $field)
	{
		$objectClassName = $field->getEntity()->getObjectClassName();
		$dataType = $field->getRefEntity()->getObjectClass();

		list($lName, $uName) = static::getFieldNameCamelCase($field->getName());

		$objectCode = [];
		$collectionCode = [];

		$objectCode[] = "\t * @method {$dataType} get{$uName}()";
		$objectCode[] = "\t * @method {$dataType} actual{$uName}()";
		$objectCode[] = "\t * @method {$dataType} require{$uName}()";

		$objectCode[] = "\t * @method {$objectClassName} set{$uName}({$dataType} \$object)";
		$objectCode[] = "\t * @method {$objectClassName} reset{$uName}()";
		$objectCode[] = "\t * @method {$objectClassName} unset{$uName}()";

		$collectionCode[] = "\t * @method {$dataType}[] get{$uName}List()";

		$objectCode[] = "\t * @method {$dataType} fill{$uName}()";
		$collectionCode[] = "\t * @method fill{$uName}()";

		return [$objectCode, $collectionCode];
	}

	public static function annotateOneToMany(OneToMany $field)
	{
		$objectClassName = $field->getEntity()->getObjectClassName();
		$collectionDataType = $field->getRefEntity()->getCollectionClass();
		$objectDataType = $field->getRefEntity()->getObjectClass();
		$objectVarName = lcfirst($field->getRefEntity()->getName());

		list($lName, $uName) = static::getFieldNameCamelCase($field->getName());

		$objectCode = [];
		$collectionCode = [];

		$objectCode[] = "\t * @method {$collectionDataType} get{$uName}()";
		$objectCode[] = "\t * @method {$collectionDataType} require{$uName}()";
		$objectCode[] = "\t * @method {$collectionDataType} fill{$uName}()";

		$objectCode[] = "\t * @method void addTo{$uName}({$objectDataType} \${$objectVarName})";
		$objectCode[] = "\t * @method void removeFrom{$uName}({$objectDataType} \${$objectVarName})";
		$objectCode[] = "\t * @method void removeAll{$uName}()";

		$objectCode[] = "\t * @method {$objectClassName} reset{$uName}()";
		$objectCode[] = "\t * @method {$objectClassName} unset{$uName}()";

		$collectionCode[] = "\t * @method {$collectionDataType}[] get{$uName}List()";
		$collectionCode[] = "\t * @method void fill{$uName}()";

		return [$objectCode, $collectionCode];
	}

	public static function annotateManyToMany(ManyToMany $field)
	{
		$objectClassName = $field->getEntity()->getObjectClassName();
		$collectionDataType = $field->getRefEntity()->getCollectionClass();
		$objectDataType = $field->getRefEntity()->getObjectClass();
		$objectVarName = lcfirst($field->getRefEntity()->getName());

		list($lName, $uName) = static::getFieldNameCamelCase($field->getName());

		$objectCode = [];
		$collectionCode = [];

		$objectCode[] = "\t * @method {$collectionDataType} get{$uName}()";
		$objectCode[] = "\t * @method {$collectionDataType} require{$uName}()";
		$objectCode[] = "\t * @method {$collectionDataType} fill{$uName}()";

		$objectCode[] = "\t * @method void addTo{$uName}({$objectDataType} \${$objectVarName})";
		$objectCode[] = "\t * @method void removeFrom{$uName}({$objectDataType} \${$objectVarName})";
		$objectCode[] = "\t * @method void removeAll{$uName}()";

		$objectCode[] = "\t * @method {$objectClassName} reset{$uName}()";
		$objectCode[] = "\t * @method {$objectClassName} unset{$uName}()";

		$collectionCode[] = "\t * @method {$collectionDataType}[] get{$uName}List()";
		$collectionCode[] = "\t * @method fill{$uName}()";

		return [$objectCode, $collectionCode];
	}

	protected static function getFieldNameCamelCase($fieldName)
	{
		$upperFirstName = Entity::snake2camel($fieldName);
		$lowerFirstName = lcfirst($upperFirstName);

		return [$lowerFirstName, $upperFirstName];
	}

	public static function scalarFieldToTypeHint($field)
	{
		if (is_string($field))
		{
			$fieldClass = $field;
		}
		else
		{
			$fieldClass = get_class($field);
		}

		switch ($fieldClass)
		{
			case DateField::class:
				return '\\'.Date::class;
			case DatetimeField::class:
				return '\\'.DateTime::class;
			case IntegerField::class:
				return '\\int';
			case BooleanField::class:
				return '\\boolean';
			case FloatField::class:
				return '\\float';
			default:
				return '\\string';
		}
	}

	protected function debug(OutputInterface $output, $message)
	{
		if ($this->debug)
		{
			$output->writeln($message);
		}
	}
}