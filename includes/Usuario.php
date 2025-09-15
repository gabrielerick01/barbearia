@@ .. @@
     // Autenticar usuário por usuário e senha
     function authenticate($usuario, $senha) {
-        $query = "SELECT id, nome, usuario, senha_hash, perfil, ativo 
+        $query = "SELECT id, nome, usuario, senha_hash, perfil, ativo
                  FROM " . $this->table_name . " 
-                 WHERE usuario = :usuario AND ativo = 1 LIMIT 1";
+                 WHERE usuario = :usuario AND ativo = 1 
+                 LIMIT 1";
         
         $stmt = $this->conn->prepare($query);
         $stmt->bindParam(':usuario', $usuario);
         $stmt->execute();
         
         if($stmt->rowCount() > 0) {
             $row = $stmt->fetch(PDO::FETCH_ASSOC);
             
             // Verificar senha
             if(password_verify($senha, $row['senha_hash'])) {
                 $this->id = $row['id'];
                 $this->nome = $row['nome'];
                 $this->usuario = $row['usuario'];
                 $this->perfil = $row['perfil'];
+                $this->ativo = $row['ativo'];
                 
                 // Atualizar último login
                 $this->updateLastLogin();
                 
                 return true;
             }
         }
         return false;
     }