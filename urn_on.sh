#!/bin/bash
# APIDIAN - Copiar templates XML personalizados y patches UBL21
# NO copiar Request.php (incompatible con Laravel 10 / Symfony 6.4)

cp resources/templates/xml/urn/*.blade.php resources/templates/xml/ 2>/dev/null || true
cp vendor/ubl21dian/torresoftware/src/XAdES/urn/*.* vendor/ubl21dian/torresoftware/src/XAdES/ 2>/dev/null || true
