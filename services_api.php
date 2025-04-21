<?php

require_once 'db_connect.php';

try {
    $stmt = $pdo->prepare("
      SELECT OfferingID, OfferingName, ServiceDescription,
             MinPrice, MaxPrice, ImagePath
      FROM ServiceOffering
      ORDER BY OfferingID DESC
    ");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error'=>'Database error']);
    exit();
}

$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/').'/';
$out = array_map(function($r) use($base) {
    $img = $r['ImagePath'] ?: 'pictures/default.png';
    if (!preg_match('#^(https?://|/)#',$img)) {
        $img = $base . $img;
    }
    return [
        'id'          => "service-{$r['OfferingID']}",
        'title'       => $r['OfferingName'],
        'description' => $r['ServiceDescription'],
        'price'       => '$'.number_format($r['MinPrice'],2)
                         .' – $'.number_format($r['MaxPrice'],2),
        'image'       => $img
    ];
}, $rows);

header('Content-Type: application/json');
echo json_encode($out, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
exit();
