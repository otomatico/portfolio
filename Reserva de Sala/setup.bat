@echo off
setlocal enabledelayedexpansion

set "ROOT_DIR=%~dp0"
set "FRONTEND_DIR=%ROOT_DIR%src\frontend"

set "REQUIRED_PHP=8.0"
set "REQUIRED_NODE=18"

echo ========================================
echo   Instalacion - Salas de Formacion
echo ========================================
echo.

:: ── Detectar gestor de paquetes ──
set "PKG_MANAGER="
where winget >nul 2>nul
if %errorlevel% equ 0 (
    set "PKG_MANAGER=winget"
    echo [INFO] Gestor detectado: winget
) else (
    where choco >nul 2>nul
    if %errorlevel% equ 0 (
        set "PKG_MANAGER=choco"
        echo [INFO] Gestor detectado: Chocolatey
    ) else (
        echo [ERR] No se detecto winget ni Chocolatey.
        echo       Instala Chocolatey desde https://chocolatey.org/install
        echo       o usa winget (incluido en Windows 10/11).
        pause
        exit /b 1
    )
)
echo.

:: ── PHP ──
echo [INFO] Verificando PHP...
where php >nul 2>nul
if %errorlevel% equ 0 (
    php -v | findstr /i "PHP" >nul
    if !errorlevel! equ 0 (
        echo [OK] PHP encontrado
    )
) else (
    echo [WARN] PHP no encontrado. Instalando...
    if "%PKG_MANAGER%"=="winget" (
        winget install --id PHP.PHP.8.3 -e --source winget
    ) else (
        choco install php -y --params "/InstallDir:%ProgramFiles%\PHP"
    )
)

:: Verificar extensiones PHP
echo [INFO] Verificando extensiones PHP...
php -m | findstr /i "pdo_sqlite" >nul
if %errorlevel% neq 0 (
    echo [WARN] Extension pdo_sqlite no encontrada.
    echo        Verifica que este habilitada en php.ini: extension=pdo_sqlite
)
php -m | findstr /i "json" >nul
if %errorlevel% neq 0 (
    echo [WARN] Extension json no encontrada.
)
echo [OK] Extensiones PHP verificadas
echo.

:: ── SQLite ──
echo [INFO] Verificando SQLite3...
where sqlite3 >nul 2>nul
if %errorlevel% equ 0 (
    echo [OK] SQLite3 encontrado
) else (
    echo [WARN] SQLite3 no encontrado. Instalando...
    if "%PKG_MANAGER%"=="winget" (
        winget install --id SQLite.SQLite -e --source winget
    ) else (
        choco install sqlite -y
    )
)
echo.

:: ── Node.js ──
echo [INFO] Verificando Node.js...
where node >nul 2>nul
if %errorlevel% equ 0 (
    node --version | findstr /i "v" >nul
    if !errorlevel! equ 0 (
        echo [OK] Node.js encontrado
    )
) else (
    echo [WARN] Node.js no encontrado. Instalando...
    if "%PKG_MANAGER%"=="winget" (
        winget install --id OpenJS.NodeJS.LTS -e --source winget
    ) else (
        choco install nodejs-lts -y
    )
)

where npm >nul 2>nul
if %errorlevel% equ 0 (
    echo [OK] npm encontrado
) else (
    echo [WARN] npm no encontrado. Revisa la instalacion de Node.js.
)
echo.

:: ── Frontend ──
echo [INFO] Instalando dependencias del frontend...
if exist "%FRONTEND_DIR%\package.json" (
    cd /d "%FRONTEND_DIR%"
    call npm install
    if !errorlevel! equ 0 (
        echo [OK] Dependencias del frontend instaladas
    ) else (
        echo [ERR] Error al instalar dependencias del frontend
    )
) else (
    echo [WARN] No se encontro %FRONTEND_DIR%\package.json
)
echo.

echo ========================================
echo [OK]  Instalacion completada
echo.
echo   Para iniciar el backend:
echo     cd src\backend | php -S localhost:5000 -t public/
echo.
echo   Para iniciar el frontend:
echo     cd src\frontend | npm run dev
echo ========================================

pause
