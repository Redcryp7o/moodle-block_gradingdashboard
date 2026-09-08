@echo off
setlocal EnableExtensions

REM ============================================================
REM EncryptEdge Labs
REM Grading Dashboard - Moodle Plugin QA
REM
REM Mandatory Moodle Plugin CI checks:
REM   1. PHP Lint
REM   2. Moodle Code Checker (PHPCS)
REM   3. Moodle PHPDoc Checker
REM   4. Plugin Validation
REM   5. Upgrade Savepoints
REM   6. Mustache Lint
REM   7. ESLint / AMD source validation
REM
REM Conditional:
REM   PHPUnit - only when tests\ exists
REM   Behat   - only when features\ exists
REM
REM PHPMD is intentionally not a failure gate.
REM
REM NOTE:
REM The Moodle Plugin CI Grunt wrapper does not work correctly in
REM this local Windows Moodle /public environment. Therefore the
REM plugin's AMD source files are checked directly with ESLint.
REM ============================================================

set "PLUGIN=D:\CODE\Moodle Plugins\Grading Dashboard"
set "COMPONENT=block_gradingdashboard"
set "MOODLE=D:\CODE\moodle-dev"
set "MOODLECI=D:\CODE\moodle-plugin-ci"

REM ------------------------------------------------------------
REM Windows compatibility
REM Mustache tooling requires Git's env.exe on Windows.
REM ------------------------------------------------------------
set "PATH=C:\Program Files\Git\usr\bin;%PATH%"

cd /d "%PLUGIN%"

echo.
echo ============================================================
echo   Grading Dashboard - Moodle Plugin QA
echo ============================================================
echo.
echo Plugin:     %PLUGIN%
echo Moodle:     %MOODLE%
echo Plugin CI:  %MOODLECI%
echo.

REM ============================================================
REM 1. PHP LINT
REM ============================================================

echo ============================================================
echo [1/8] PHP Lint
echo ============================================================
php "%MOODLECI%\bin\moodle-plugin-ci" phplint "%PLUGIN%"
if errorlevel 1 goto :failed

REM ============================================================
REM 2. MOODLE CODE CHECKER
REM ============================================================

echo.
echo ============================================================
echo [2/8] Moodle Code Checker
echo ============================================================
php "%MOODLECI%\bin\moodle-plugin-ci" phpcs --max-warnings 0 "%PLUGIN%"
if errorlevel 1 goto :failed

REM ============================================================
REM 3. PHPDOC
REM ============================================================

echo.
echo ============================================================
echo [3/8] Moodle PHPDoc Checker
echo ============================================================
php "%MOODLECI%\bin\moodle-plugin-ci" phpdoc --moodle="%MOODLE%" --max-warnings 0 "%PLUGIN%"
if errorlevel 1 goto :failed

REM ============================================================
REM 4. PLUGIN VALIDATION
REM ============================================================

echo.
echo ============================================================
echo [4/8] Plugin Validation
echo ============================================================
php "%MOODLECI%\bin\moodle-plugin-ci" validate --moodle="%MOODLE%" "%PLUGIN%"
if errorlevel 1 goto :failed

REM ============================================================
REM 5. SAVEPOINTS
REM ============================================================

echo.
echo ============================================================
echo [5/8] Upgrade Savepoints
echo ============================================================
php "%MOODLECI%\bin\moodle-plugin-ci" savepoints "%PLUGIN%"
if errorlevel 1 goto :failed

REM ============================================================
REM 6. MUSTACHE
REM
REM Moodle Plugin CI's Windows wrapper has a path-normalization
REM problem with Moodle's /public directory in this environment.
REM Therefore use the official bundled Mustache linter directly.
REM ============================================================

echo.
echo ============================================================
echo [6/8] Mustache Lint
echo ============================================================

set "MUSTACHE_LINTER=%MOODLECI%\vendor\moodlehq\moodle-local_ci\mustache_lint\mustache_lint.php"
set "VNU=%MOODLECI%\vendor\moodlehq\moodle-local_ci\node_modules\vnu-jar\build\dist\vnu.jar"
set "MUSTACHE_TEMPLATES=%MOODLE%\public\blocks\gradingdashboard\templates"

if exist "%PLUGIN%\templates" if exist "%MUSTACHE_TEMPLATES%" (
xcopy /Y /I /Q "%PLUGIN%\templates\*.mustache" "%MUSTACHE_TEMPLATES%\" >nul
)

if exist "%MUSTACHE_TEMPLATES%" (
for /r "%MUSTACHE_TEMPLATES%" %%F in (*.mustache) do (
echo Checking: %%F
php "%MUSTACHE_LINTER%" --filename="%%F" --validator="%VNU%" --basename="%MOODLE%\public"
if errorlevel 1 goto :failed
)
) else (
echo No templates\ directory found in installed plugin.
echo Mustache: NOT APPLICABLE
)

REM ============================================================
REM 7. ESLINT / AMD SOURCE
REM
REM Validate all AMD JavaScript source files directly.
REM This avoids the known local moodle-plugin-ci Grunt wrapper
REM failure while still performing the JavaScript lint check.
REM ============================================================

echo.
echo ============================================================
echo [7/8] ESLint / AMD Source
echo ============================================================

set "ESLINT=%MOODLE%\node_modules\.bin\eslint.cmd"
set "ESLINT_CONFIG=%MOODLE%\.eslintrc"

if not exist "%ESLINT%" (
echo ERROR: ESLint not found:
echo %ESLINT%
goto :failed
)

if not exist "%ESLINT_CONFIG%" (
echo ERROR: Moodle ESLint config not found:
echo %ESLINT_CONFIG%
goto :failed
)

set "AMD_FOUND=0"

for /r "%PLUGIN%\amd\src" %%F in (*.js) do (
set "AMD_FOUND=1"
echo Checking: %%F
call "%ESLINT%" -c "%ESLINT_CONFIG%" --no-eslintrc --max-warnings 0 "%%F"
if errorlevel 1 goto :failed
)

if "%AMD_FOUND%"=="0" (
echo No AMD source files found.
echo ESLint: NOT APPLICABLE
)

REM ============================================================
REM 8. PHPUNIT - CONDITIONAL
REM ============================================================

echo.
echo ============================================================
echo [8/8] PHPUnit
echo ============================================================

if exist "%PLUGIN%\tests" (
echo PHPUnit test suite detected.
php "%MOODLECI%\bin\moodle-plugin-ci" phpunit "%PLUGIN%"
if errorlevel 1 goto :failed
) else (
echo No tests\ directory found.
echo PHPUnit: NOT APPLICABLE
)

REM ============================================================
REM BEHAT - CONDITIONAL
REM ============================================================

echo.
echo ============================================================
echo Behat
echo ============================================================

if exist "%PLUGIN%\features" (
echo Behat feature tests detected.
php "%MOODLECI%\bin\moodle-plugin-ci" behat "%PLUGIN%"
if errorlevel 1 goto :failed
) else (
echo No features\ directory found.
echo Behat: NOT APPLICABLE
)

REM ============================================================
REM SUCCESS
REM ============================================================

echo.
echo ============================================================
echo   ALL APPLICABLE QA CHECKS PASSED
echo ============================================================
echo.
echo  PHP Lint             : PASS
echo  Moodle Code Checker : PASS
echo  PHPDoc              : PASS
echo  Validation          : PASS
echo  Savepoints          : PASS
echo  Mustache             : PASS
echo  ESLint / AMD         : PASS

if exist "%PLUGIN%\tests" (
echo  PHPUnit             : PASS
) else (
echo  PHPUnit             : N/A
)

if exist "%PLUGIN%\features" (
echo  Behat               : PASS
) else (
echo  Behat               : N/A
)

echo.
echo  PHPMD                 : NOT USED AS FAILURE GATE
echo  Grunt wrapper         : SKIPPED - local environment limitation
echo.
echo ============================================================
exit /b 0

REM ============================================================
REM FAILURE
REM ============================================================

:failed

echo.
echo ============================================================
echo   QA CHECK FAILED
echo ============================================================
echo.
echo Review the error reported immediately above.
echo No further QA checks were executed.
echo.
echo ============================================================
exit /b 1
