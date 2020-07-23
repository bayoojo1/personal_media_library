<?php
function get_catalog_count($category = null, $search = null) {
    $category = strtolower($category);
    include("connection.php");

    try {
        $sql = "SELECT COUNT(media_id) FROM Media";
        if(!empty($search)) {
            $stmt = $db_connect->prepare($sql . " WHERE title LIKE :title");
            $stmt->bindValue(':title', '%'.$search.'%', PDO::PARAM_STR);
        } else if(!empty($category)) {
        $stmt = $db_connect->prepare($sql . " WHERE LOWER(category) = :category");
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        } else {
            $stmt = $db_connect->prepare($sql);
        }
        $stmt->execute();
        } catch (Exception $e) {
            echo "bad query";
        }
        $count = $stmt->fetchColumn(0);
        return $count;
    }



function full_catalog_array($limit = null, $offset = 0) {
    include('connection.php');
    try {
        $sql = "SELECT media_id, title, category, img FROM Media 
        ORDER BY 
        REPLACE(
           REPLACE(
              REPLACE(title,'The ',''),
              'An ',
              ''
           ),
           'A ',
           ''
         )";
    if(is_integer($limit)) {
        $stmt = $db_connect->prepare($sql . " LIMIT :limit OFFSET :offset");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    } else {
        $stmt = $db_connect->prepare($sql);
    }
        $stmt->execute();
    } catch (Exception $e) {
        echo "Unable to retrieve results";
        exit;
    }

    $catalog = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $catalog;
}

function category_catalog_array($category, $limit = null, $offset = 0) {
    include('connection.php');
    $category = strtolower($category);
    try {
        $sql = "SELECT media_id, title, category, img FROM Media WHERE LOWER(category) = :category 
        ORDER BY 
        REPLACE(
           REPLACE(
              REPLACE(title,'The ',''),
              'An ',
              ''
           ),
           'A ',
           ''
         )";
        if(is_integer($limit)) {
            $stmt = $db_connect->prepare($sql . " LIMIT :limit OFFSET :offset");
            $stmt->bindParam(':category', $category, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        } else {
            $stmt = $db_connect->prepare($sql);
            $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        }
        $stmt->execute();
    } catch (Exception $e) {
        echo "Unable to retrieve results";
        exit;
    }

    $catalog = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $catalog;
}


function search_catalog_array($search, $limit = null, $offset = 0) {
    include('connection.php');
    try {
        $sql = "SELECT media_id, title, category, img FROM Media WHERE title LIKE :title 
        ORDER BY 
        REPLACE(
           REPLACE(
              REPLACE(title,'The ',''),
              'An ',
              ''
           ),
           'A ',
           ''
         )";
        if(is_integer($limit)) {
            $stmt = $db_connect->prepare($sql . " LIMIT :limit OFFSET :offset");
            $stmt->bindValue(':title', "%".$search."%", PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        } else {
            $stmt = $db_connect->prepare($sql);
            $stmt->bindValue(':title', "%".$search."%", PDO::PARAM_STR);
        }
        $stmt->execute();
    } catch (Exception $e) {
        echo "Unable to retrieve results";
        exit;
    }

    $catalog = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $catalog;
}


function random_catalog_array() {
    include('connection.php');
    try {
        $sql = "SELECT media_id, title, category, img FROM Media ORDER BY RAND() LIMIT 4";
        $stmt = $db_connect->prepare($sql);
        $stmt->execute();
    } catch (Exception $e) {
        echo "Unable to retrieve results";
        exit;
    }

    $catalog = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $catalog;
}

function single_item_array($id) {
    include('connection.php');
    try {
        $sql = "SELECT Media.media_id, title, img, format, year, category, genre, publisher, isbn FROM Media JOIN Genres ON Media.genre_id = Genres.genre_id LEFT OUTER JOIN Books ON Media.media_id = Books.media_id WHERE Media.media_id = :id";
        $stmt = $db_connect->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        $stmt->execute();
    } catch (Exception $e) {
        echo "Unable to retrieve results";
        exit;
    }
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if(empty($item)) return $item; // this line is called early return. No need of curly braces for the if statement

        try {
            $sql = "SELECT fullname, role FROM Media_People JOIN People ON Media_People.people_id = People.people_id WHERE Media_People.media_id = :id";
            $stmt = $db_connect->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->execute();
        } catch (Exception $e) {
            echo "Unable to retrieve results";
            exit;
        }
        foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $item[$row["role"]][] = $row["fullname"];
          }
    return $item;
}

function genre_array($category = null) {
    $category = strtolower($category);
    include("connection.php");
  
    try {
      $sql = "SELECT genre, category"
        . " FROM Genres "
        . " JOIN Genre_Categories "
        . " ON Genres.genre_id = Genre_Categories.genre_id ";
      if (!empty($category)) {
        $stmt = $db_connect->prepare($sql 
            . " WHERE LOWER(category) = :category"
            . " ORDER BY genre");
        $results->bindParam(':category', $category, PDO::PARAM_STR);
      } else {
        $stmt = $db_connect->prepare($sql . " ORDER BY genre");
      }
      $stmt->execute();
    } catch (Exception $e) {
      echo "bad query";
    }
    $genres = array();

    foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $genres[$row["category"]][] = $row["genre"];
    }
    return $genres;
  }



function get_item_html($item) {
    $output = "<li><a href='details.php?id="
        . $item["media_id"] . "'><img src='" 
        . $item["img"] . "' alt='" 
        . $item["title"] . "' />"
        . "<p style='font-size:12px; background-color:beige; color:darkslategrey'>".$item['title']."</p>"
        . "<p>View Details</p>"
        . "</a></li>";
    return $output;
}