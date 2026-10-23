<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Diagnostic EPayFC</h2>";

echo "<h3>1. PHP Version</h3>";
echo "PHP " . phpversion() . "<br>";

echo "<h3>2. Fichiers de configuration</h3>";
$dev_exists = file_exists("config/dev.ini");
$prod_exists = file_exists("config/prod.ini");
echo "config/dev.ini : " . ($dev_exists ? "Present" : "Absent") . "<br>";
echo "config/prod.ini : " . ($prod_exists ? "Present" : "Absent") . "<br>";

if ($prod_exists) {
    // Afficher le contenu brut (en masquant les 4 derniers caracteres du mot de passe)
    $raw_lines = file("config/prod.ini");
    echo "<h4>Contenu de config/prod.ini (avec numeros de lignes) :</h4><pre style='background:#f4f4f4;padding:10px;'>";
    foreach ($raw_lines as $num => $line) {
        $display_line = $line;
        if (stripos($line, 'dbpassword') !== false) {
            $display_line = preg_replace('/(=s*["']?).*(["']?)/', '$1********$2', $line);
        }
        echo htmlspecialchars(($num + 1) . ": " . $display_line);
    }
    echo "</pre>";

    $ini = @parse_ini_file("config/prod.ini");
    if ($ini === false) {
        $err = error_get_last();
        echo "<b style='color:red'>Erreur de syntaxe dans config/prod.ini : " . htmlspecialchars($err['message'] ?? 'Erreur de syntaxe') . "</b><br>";
    } else {
        echo "<b style='color:green'>config/prod.ini charge avec succes (" . count($ini) . " parametres)</b><br>";
        echo "dbhost: " . ($ini['dbhost'] ?? 'NON DEFINI') . "<br>";
        echo "dbname: " . ($ini['dbname'] ?? 'NON DEFINI') . "<br>";
        echo "dbuser: " . ($ini['dbuser'] ?? 'NON DEFINI') . "<br>";
        echo "dbpassword: " . (!empty($ini['dbpassword']) ? 'Defini (' . strlen($ini['dbpassword']) . ' caracteres)' : '<b style="color:red">VIDE</b>') . "<br>";
        
        echo "<h3>3. Test de connexion MySQL</h3>";
        try {
            $dsn = "mysql:host=" . ($ini['dbhost'] ?? '') . ";dbname=" . ($ini['dbname'] ?? '') . ";charset=utf8";
            $pdo = new PDO($dsn, $ini['dbuser'] ?? '', $ini['dbpassword'] ?? '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<b style='color:green'>Connexion MySQL reussie !</b><br>";
            
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            echo "Tables trouvees : " . implode(', ', $tables) . "<br>";
        } catch (Exception $e) {
            echo "<b style='color:red'>Erreur MySQL : " . htmlspecialchars($e->getMessage()) . "</b><br>";
        }
    }
}

echo "<h3>4. Test du Framework EPayFC</h3>";
try {
    require_once 'framework/Configuration.php';
    require_once 'framework/Router.php';
    echo "Framework charge avec succes.<br>";
} catch (Throwable $t) {
    echo "<b style='color:red'>Erreur framework : " . htmlspecialchars($t->getMessage()) . "</b><br>";
}
