<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    header('Access-Control-Allow-Headers: Origin, Accept, Content-Type, X-Requested-With');
    exit();
}


// Database configuration
$dbHost = 'localhost';
$dbName = 'quotesdb';
$dbUsername = 'tai_britt';
$dbPassword = 'tai_password';

// Define database connection
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUsername, $dbPassword);
    // Set PDO to throw exceptions on errors
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Define base URL
$baseURL = "https://tai-britt.hostname.com/api";

// Define response content type
header("Content-Type: application/json");

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
        $quotations = fetchData($query);
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
    }

    if ($categories) {
        echo json_encode($categories);
    } else {
        echo json_encode(["message" => "category_id Not Found"]);
    }
}

// Function to handle POST requests
function createResource($resource, $data) {
    global $pdo;
    switch ($resource) {
        case 'quote':
            // Validate required parameters
            if (!isset($data['quote']) || !isset($data['author_id']) || !isset($data['category_id'])) {
                echo json_encode(["message" => "Missing Required Parameters"]);
                return;
            }
            // Insert new quote into database
            $query = "INSERT INTO quotes (quote, author_id, category_id) VALUES (:quote, :author_id, :category_id)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':quote' => $data['quote'], ':author_id' => $data['author_id'], ':category_id' => $data['category_id']]);
            echo json_encode(["message" => "Quote created successfully"]);
            break;
        case 'author':
            // Validate required parameter
            if (!isset($data['author'])) {
                echo json_encode(["message" => "Missing Required Parameters"]);
                return;
            }
            // Insert new author into database
            $query = "INSERT INTO authors (author) VALUES (:author)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':author' => $data['author']]);
            echo json_encode(["message" => "Author created successfully"]);
            break;
        case 'category':
            // Validate required parameter
            if (!isset($data['category'])) {
                echo json_encode(["message" => "Missing Required Parameters"]);
                return;
            }
            // Insert new category into database
            $query = "INSERT INTO categories (category) VALUES (:category)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':category' => $data['category']]);
            echo json_encode(["message" => "Category created successfully"]);
            break;
        default:
            echo json_encode(["message" => "Invalid resource"]);
            break;
    }
}

// Main entry point - handle incoming requests

// Parse request URI
$requestURI = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];
$resource = strtok($requestURI, '?');
$params = $_GET;

// Route requests to appropriate functions based on method and resource
switch ($method) {
    case 'GET':
        if ($resource == '/quotes/') {
            getQuotations($params);
        } elseif ($resource == '/authors/') {
            getAuthors($params);
        } elseif ($resource == '/categories/') {
            getCategories($params);
        } else {
            // Handle invalid endpoint
            http_response_code(404);
            echo json_encode(["message" => "Invalid endpoint"]);
        }
        break;
    case 'POST':
        if ($resource == '/quotes/') {
            createResource('quote', $_POST);
        } elseif ($resource == '/authors/') {
            createResource('author', $_POST);
        } elseif ($resource == '/categories/') {
            createResource('category', $_POST);
        } else {
            // Handle invalid endpoint
            http_response_code(404);
            echo json_encode(["message" => "Invalid endpoint"]);
        }
        break;
    default:
        // Handle unsupported methods
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
}
