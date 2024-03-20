<?php

header('Access-Control-Allow-Origin: *');
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    header('Access-Control-Allow-Headers: Origin, Accept, Content-Type, X-Requested-With');
    exit();
}

// Database configuration
$dbHost = 'dpg-cntiscfsc6pc73cbm6dg-a.oregon-postgres.render.com';
$dbName = 'tai_britt_midterm';
$dbUsername = 'tai_britt_midterm_user';
$dbPassword = '38jf4qjbQk5IRu5qgLxksRY1ryrKb8gh';

// Define database connection
try {
    $pdo = new PDO("pgsql:host=$dbHost;dbname=$dbName", $dbUsername, $dbPassword);
    // Set PDO to throw exceptions on errors
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection error: " . $e->getMessage()]);
    exit();
}

// Define base URL
$baseURL = "https://php-midterm-project.onrender.com/api/";

// Function to fetch data from the database
function fetchData($query, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to handle GET requests for quotations
function getQuotations($params) {
    global $pdo;
    $query = "SELECT quotes.id, quotes.quote, authors.author, categories.category 
              FROM quotes 
              INNER JOIN authors ON quotes.author_id = authors.id 
              INNER JOIN categories ON quotes.category_id = categories.id";

    if (!empty($params['id'])) {
        $query .= " WHERE quotes.id = :id";
        $quotations = fetchData($query, [':id' => $params['id']]);
    } elseif (!empty($params['author_id']) && !empty($params['category_id'])) {
        $query .= " WHERE quotes.author_id = :author_id AND quotes.category_id = :category_id";
        $quotations = fetchData($query, [':author_id' => $params['author_id'], ':category_id' => $params['category_id']]);
    } elseif (!empty($params['author_id'])) {
        $query .= " WHERE quotes.author_id = :author_id";
        $quotations = fetchData($query, [':author_id' => $params['author_id']]);
    } elseif (!empty($params['category_id'])) {
        $query .= " WHERE quotes.category_id = :category_id";
        $quotations = fetchData($query, [':category_id' => $params['category_id']]);
    } else {
        // Ensure minimum of 25 quotes are returned
        $quotations = fetchData($query);
        if (count($quotations) < 25) {
            http_response_code(500);
            echo json_encode(["message" => "Minimum of 25 quotes not found"]);
            exit();
        }
    }

    if ($quotations) {
        echo json_encode($quotations);
    } else {
        echo json_encode(["message" => "No Quotes Found"]);
    }
}

// Function to handle GET requests for authors
function getAuthors($params) {
    global $pdo;
    $query = "SELECT * FROM authors";

    if (!empty($params['id'])) {
        $query .= " WHERE id = :id";
        $authors = fetchData($query, [':id' => $params['id']]);
    } else {
        $authors = fetchData($query);
        if (count($authors) < 5) {
            http_response_code(500);
            echo json_encode(["message" => "Minimum of 5 authors not found"]);
            exit();
        }
    }

    if ($authors) {
        echo json_encode($authors);
    } else {
        echo json_encode(["message" => "author_id Not Found"]);
    }
}

// Function to handle GET requests for categories
function getCategories($params) {
    global $pdo;
    $query = "SELECT * FROM categories";

    if (!empty($params['id'])) {
        $query .= " WHERE id = :id";
        $categories = fetchData($query, [':id' => $params['id']]);
    } else {
        $categories = fetchData($query);
        if (count($categories) < 5) {
            http_response_code(500);
            echo json_encode(["message" => "Minimum of 5 categories not found"]);
            exit();
        }
    }

    if ($categories) {
        echo json_encode($categories);
    } else {
        echo json_encode(["message" => "category_id Not Found"]);
    }
}

// Rest of the code remains unchanged

?>
