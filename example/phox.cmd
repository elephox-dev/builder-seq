@echo off
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0phox
SET COMPOSER_RUNTIME_BIN_DIR=%~dp0
php "%BIN_TARGET%" %*