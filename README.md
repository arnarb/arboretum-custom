# Arboretum

This is a custom plugin for all custom features on the Arnold Arboretum website

## Table of Contents

- [Arboretum](#arboretum-starter)
    - [Table of Contents](#table-of-contents)
    - [Setup](#setup)

## Setup

This requires [PHP Spreadsheet](https://phpspreadsheet.readthedocs.io/en/latest/) to compose spreadsheet readouts for [Modern Event Calendar](https://webnus.net/modern-events-calendar/) event bookins (as of 1-22-2024 this was the event system plugin we used).

Installation steps:

1. In the command line navigate to plugin's root directory (/www/arnoldarboretumwebsite_753/public/wp-content/plugins/arboretum-custom).

2. Enter commands:

    -composer require phpoffice/phpspreadsheet
    -composer install

3. To call from the plugin add the following to the file's header:

```
<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
```
