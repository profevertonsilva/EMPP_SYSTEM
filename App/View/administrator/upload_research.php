<?php


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $directoryDestination = 'resources/dashboard/assets/img/research/';
    
    // Cria o diretório se não existir
    if (!is_dir($directoryDestination)) {
        mkdir($directoryDestination, 0755, true);
    }

    if (isset($_FILES['ree_file'])) {
        $file = $_FILES['ree_file'];
        
        // Verifica se houve erro no upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            die("Erro no upload do arquivo.");
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = hash('md5', uniqid());
        $destinationPath = $directoryDestination . $filename . '.jpg'; // Sempre salva como JPG

        // Tipos permitidos (TIFF descontinuado)
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($file['type'], $allowedTypes) && in_array($extension, $allowedExtensions)) {
            // Aqui você deveria implementar a conversão para JPG
            // Por enquanto, apenas move o arquivo
            if (move_uploaded_file($file['tmp_name'], $destinationPath)) {
                $_SESSION['ree_file'] = $filename.'.jpg';
                
                if (isset($_ENV['BASE_URL'])) {
                    header('Location: '.$_ENV['BASE_URL'].'dashboard/researcher/research/info');
                    exit;
                } else {
                    die("BASE_URL não está definido.");
                }
            } else {
                die("Erro ao mover o arquivo.");
            }
        } else {
            die("Formato de arquivo não permitido.");
        }
    } else {
        die("Nenhum arquivo foi enviado.");
    }
}

//https://products.groupdocs.cloud/pt/conversion/python/tiff-to-jpg/#accept Verificar depois