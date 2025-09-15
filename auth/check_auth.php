@@ .. @@
 function checkAuthentication() {
-    if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
+    // Verificar se a sessão existe e está válida
+    if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || !isset($_SESSION['user_id'])) {
         // Usuário não está logado, redirecionar para login
         $current_path = $_SERVER['REQUEST_URI'];
-        if (strpos($current_path, '/auth/') === false) {
+        $base_path = dirname($_SERVER['SCRIPT_NAME']);
+        
+        if (strpos($current_path, '/auth/') === false && strpos($current_path, 'login.php') === false) {
             header('Location: auth/login.php');
         } else {
             header('Location: login.php');
         }
         exit();
     }
     
     // Verificar se usuário ainda está ativo no banco
     if (isset($_SESSION['user_id'])) {
-        require_once(__DIR__ . '/../config/database.php');
-        require_once(__DIR__ . '/../includes/Usuario.php');
-        
-        $database = new Database();
-        $db = $database->getConnection();
-        $usuario = new Usuario($db);
-        $usuario->id = $_SESSION['user_id'];
-        
-        if (!$usuario->readOne() || !$usuario->ativo) {
-            // Usuário foi desativado, fazer logout
-            session_destroy();
-            header('Location: auth/login.php');
-            exit();
+        try {
+            require_once(__DIR__ . '/../config/database.php');
+            require_once(__DIR__ . '/../includes/Usuario.php');
+            
+            $database = new Database();
+            $db = $database->getConnection();
+            
+            if ($db) {
+                $usuario = new Usuario($db);
+                $usuario->id = $_SESSION['user_id'];
+                
+                if (!$usuario->readOne() || !$usuario->ativo) {
+                    // Usuário foi desativado, fazer logout
+                    session_unset();
+                    session_destroy();
+                    header('Location: auth/login.php');
+                    exit();
+                }
+            }
+        } catch (Exception $e) {
+            // Em caso de erro, manter usuário logado mas registrar erro
+            error_log("Erro na verificação de autenticação: " . $e->getMessage());
         }
     }
 }