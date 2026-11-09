<?php
/**
 * CIA - Compañía de Arte y Vida
 * API REST para publicaciones, suscriptores y contactos
 * 
 * Endpoints disponibles:
 * GET  /api.php?action=get_publications&page=talleres|shows
 * GET  /api.php?action=get_all_publications
 * POST /api.php?action=create_publication
 * POST /api.php?action=delete_publication
 * GET  /api.php?action=get_subscribers
 * POST /api.php?action=subscribe
 * POST /api.php?action=contact
 * GET  /api.php?action=get_contact_messages
 * POST /api.php?action=upload_gallery
 * GET  /api.php?action=get_gallery
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Incluir configuración de base de datos
require_once 'config.php';

// Obtener método y acción
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Responder según el método y acción
switch ($action) {
    
    // ==========================================
    // PUBLICACIONES
    // ==========================================
    
    case 'get_publications':
        // Obtener publicaciones por página (talleres o shows)
        $page = $_GET['page'] ?? '';
        if (!in_array($page, ['talleres', 'shows'])) {
            echo json_encode(['success' => false, 'error' => 'Página inválida']);
            exit;
        }
        
        $stmt = $pdo->prepare("SELECT * FROM publications WHERE page = ? ORDER BY date DESC");
        $stmt->execute([$page]);
        $publications = $stmt->fetchAll();
        
        // Convertir tags JSON a array
        foreach ($publications as &$pub) {
            $pub['tags'] = $pub['tags'] ? json_decode($pub['tags'], true) : [];
        }
        
        echo json_encode(['success' => true, 'data' => $publications]);
        break;
        
    case 'get_all_publications':
        // Obtener todas las publicaciones (para el admin)
        $stmt = $pdo->query("SELECT * FROM publications ORDER BY created_at DESC");
        $publications = $stmt->fetchAll();
        
        foreach ($publications as &$pub) {
            $pub['tags'] = $pub['tags'] ? json_decode($pub['tags'], true) : [];
        }
        
        echo json_encode(['success' => true, 'data' => $publications]);
        break;
        
    case 'create_publication':
        // Crear nueva publicación (desde admin)
        if ($method !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $page = $input['page'] ?? '';
        $title = trim($input['title'] ?? '');
        $description = trim($input['description'] ?? '');
        
        if (!$page || !$title || !$description) {
            echo json_encode(['success' => false, 'error' => 'Faltan datos obligatorios']);
            exit;
        }
        
        $image = $input['image'] ?? 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=600&q=80';
        $price = $input['price'] ?? '';
        $duration = $input['duration'] ?? '';
        $date = $input['date'] ?? date('Y-m-d');
        $tags = $input['tags'] ?? [];
        
        $stmt = $pdo->prepare("
            INSERT INTO publications (page, title, description, image, price, duration, date, tags) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$page, $title, $description, $image, $price, $duration, $date, json_encode($tags)]);
        
        $id = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Publicación creada correctamente',
            'id' => $id
        ]);
        break;
        
    case 'delete_publication':
        // Eliminar publicación (desde admin)
        if ($method !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? 0;
        
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID inválido']);
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM publications WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true, 'message' => 'Publicación eliminada']);
        break;
    
    // ==========================================
    // SUSCRIPTORES
    // ==========================================
    
    case 'get_subscribers':
        // Obtener lista de suscriptores (para admin)
        $stmt = $pdo->query("SELECT * FROM subscribers ORDER BY created_at DESC");
        $subscribers = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'data' => $subscribers]);
        break;
        
    case 'subscribe':
        // Suscribirse al newsletter
        if ($method !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $email = trim($input['email'] ?? '');
        
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'Email inválido']);
            exit;
        }
        
        // Verificar si ya está suscrito
        $stmt = $pdo->prepare("SELECT id FROM subscribers WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Este correo ya está suscrito']);
            exit;
        }
        
        // Insertar nuevo suscriptor
        $stmt = $pdo->prepare("INSERT INTO subscribers (email) VALUES (?)");
        $stmt->execute([$email]);
        
        echo json_encode(['success' => true, 'message' => 'Suscripción exitosa']);
        break;
        
    case 'get_subscriber_count':
        // Obtener número de suscriptores
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM subscribers");
        $result = $stmt->fetch();
        
        echo json_encode(['success' => true, 'count' => $result['total']]);
        break;
    
    // ==========================================
    // MENSAJES DE CONTACTO
    // ==========================================
    
    case 'contact':
        // Enviar mensaje de contacto
        if ($method !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $message = trim($input['message'] ?? '');
        
        if (!$name || !$email || !$message) {
            echo json_encode(['success' => false, 'error' => 'Todos los campos son obligatorios']);
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'Email inválido']);
            exit;
        }
        
        // Guardar mensaje en la base de datos
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $message]);
        
        echo json_encode(['success' => true, 'message' => 'Mensaje enviado correctamente']);
        break;
        
    case 'get_contact_messages':
        // Obtener mensajes de contacto (para admin)
        $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
        $messages = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'data' => $messages]);
        break;
    
    // ==========================================
    // GALERÍA DE IMÁGENES
    // ==========================================
    
    case 'upload_gallery':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            exit;
        }

        if (!isset($_FILES['media-upload'])) {
            echo json_encode(['success' => false, 'error' => 'No se recibió ningún archivo']);
            exit;
        }

        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $file = $_FILES['media-upload'];
        $fileName = time() . '_' . basename($file['name']);
        $targetPath = $uploadDir . $fileName;
        $fileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));

        // Validar extensiones
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4'];
        if (!in_array($fileType, $allowed)) {
            echo json_encode(['success' => false, 'error' => 'Formato no permitido']);
            exit;
        }

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $title = $_POST['title'] ?? '';
            $stmt = $pdo->prepare("INSERT INTO gallery_images (image_url, title) VALUES (?, ?)");
            $stmt->execute([$targetPath, $title]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Archivo subido correctamente',
                'url' => $targetPath
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al mover el archivo']);
        }
        break;

    case 'get_gallery':
        $stmt = $pdo->query("SELECT * FROM gallery_images ORDER BY created_at DESC");
        $images = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $images]);
        break;

    case 'delete_gallery_item':
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? 0;
        
        $stmt = $pdo->prepare("SELECT image_url FROM gallery_images WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        
        if ($item && file_exists($item['image_url'])) {
            unlink($item['image_url']);
        }
        
        $stmt = $pdo->prepare("DELETE FROM gallery_images WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    // ==========================================
    // ESTADÍSTICAS (para admin)
    // ==========================================
    
    case 'get_stats':
        // Obtener estadísticas del sitio
        $pubs = $pdo->query("SELECT COUNT(*) as total FROM publications")->fetch()['total'];
        $subs = $pdo->query("SELECT COUNT(*) as total FROM subscribers")->fetch()['total'];
        $msgs = $pdo->query("SELECT COUNT(*) as total FROM contact_messages")->fetch()['total'];
        
        echo json_encode([
            'success' => true,
            'data' => [
                'publications' => $pubs,
                'subscribers' => $subs,
                'messages' => $msgs
            ]
        ]);
        break;
    
    // ==========================================
    // DEFAULT
    // ==========================================
    
    default:
        echo json_encode([
            'success' => false, 
            'error' => 'Acción no válida',
            'available_actions' => [
                'get_publications',
                'get_all_publications', 
                'create_publication',
                'delete_publication',
                'get_subscribers',
                'subscribe',
                'contact',
                'get_contact_messages',
                'get_stats'
            ]
        ]);
}
?>
