# bxApiDocs

Всеми любимый bxApiDocs стал редко обновляться, поэтому запилили свой

>Это сама по себе папка с модулями /bitrix/modules/, 1С-Битрикс24: Корпоративный портал, но с убранными лишними файлами и папками (не .php, без классов методов, констант и т.д.), добавленными константами, событиями и хелпами phpDocs.


## Как использовать

### Composer
```bash
composer require matiaspub/bx-api-docs --dev
```

### PhpStorm
В настройках PHP IDE PhpStorm (File -> Settings -> Default Settings -> PHP или File -> Settings -> Languages & Frameworks -> PHP ) области Include Path нажав на "+" добавляем путь к папке ```modules```.

Примечание: в первом случае добавляется папка ```modules``` для всех новых проектов, во втором - для текущего проекта.