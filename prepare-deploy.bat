@echo off
chcp 65001 >nul
echo ============================================
echo   MenuPro - Préparation pour hébergement
echo ============================================
echo.

echo [1/3] Installation des dépendances PHP (production)...
call composer install --no-dev --optimize-autoloader
if errorlevel 1 (
    echo ERREUR: composer install a échoué.
    pause
    exit /b 1
)
echo OK.
echo.

echo [2/3] Installation des dépendances Node et compilation des assets...
if not exist "node_modules" (
    call npm ci
) else (
    echo node_modules présent, npm run build uniquement...
)
call npm run build
if errorlevel 1 (
    echo ERREUR: npm run build a échoué.
    pause
    exit /b 1
)
echo OK.
echo.

echo [3/3] Vérifications...
if not exist "vendor\autoload.php" (
    echo ERREUR: vendor/ manquant.
    pause
    exit /b 1
)
if not exist "public\build\manifest.json" (
    echo ATTENTION: public/build/ manquant ou incomplet. Vérifiez npm run build.
) else (
    echo public/build/ présent.
)
echo.

echo ============================================
echo   Préparation terminée.
echo ============================================
echo.
echo Prochaine étape : créer une archive ou uploader
echo les fichiers en EXCLUANT (voir docs/PREPARATION_HEBERGEMENT.md) :
echo   - .env
echo   - node_modules
echo   - .git
echo.
echo Sur le serveur (public_html + Menupro) : créer le lien
echo   public_html/storage -^> ../Menupro/storage/app/public
echo pour que les images uploadées s'affichent (voir doc section 6).
echo.
pause
    