<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    header('Access-Control-Allow-Headers: Origin, Accept, Content-Type, X-Requested-With');
    exit();
}


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
