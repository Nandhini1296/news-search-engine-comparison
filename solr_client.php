<?php
header('Content-Type: text/html; charset=utf-8');
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;
$ranking = isset($_REQUEST['ranking']) ? $_REQUEST['ranking'] : "lucene";


if ($ranking == "pagerank") {
    $addParams = array(
        'sort' => 'pageRankFile desc'
    );
} else {
    $addParams = array();
}

if ($query) {
    require_once('solr-php-client-master/Apache/Solr/Service.php');
    $solr = new Apache_Solr_Service('localhost', 8983, '/solr/assignment4/');

    if (get_magic_quotes_gpc() == 1) {
        $query = stripslashes($query);
    }

    try {
        $results = $solr->search($query, 0, $limit, $addParams);
    } catch (Exception $e) {
        die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
    }
}
?>

<html>

<head>
    <title>PHP Solr Search Engine Client</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <style>
        .inline-icon {
            vertical-align: bottom;
            font-size: 24px !important;
        }

        i {
            padding: 6px 3px;
        }

        a:visited {
            color: #5AD3E7;
        }

        .form_container {
            margin: 0 auto;
            text-align: center;
            margin-top: 40px;
        }

        .gap {
            width: 50px;
        }

        .dropdown-menu {
            width: 20px !important;
            height: 60px !important;
        }

        .results {
            text-align: center;
        }

        ol {
            padding-right: 40px;
        }

        th {
            width: 10%;
        }
    </style>
</head>

<body>
    <div class="form_container">
        <form class="form-inline d-flex justify-content-center md-form form-sm mt-0" accept-charset="utf-8" method="get">
            <i class="inline-icon material-icons">search</i>
            <input class="form-control form-control-sm ml-3 w-75" id="q" name="q" type="text" placeholder="Search" aria-label="Search" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>" />
            <label class="sr-only" for="inlineFormInputGroupUsername2">Username</label>&nbsp;&nbsp;

            <?php if ($ranking == "lucene") { ?>
                <input type="radio" value="lucene" name="ranking" checked>&nbsp;Lucene&nbsp;&nbsp;&nbsp;
                <input type="radio" value="pagerank" name="ranking">&nbsp;Pagerank&nbsp;&nbsp;&nbsp;

            <?php } else { ?>
                <input type="radio" value="lucene" name="ranking">&nbsp;Lucene&nbsp;&nbsp;&nbsp;
                <input type="radio" value="pagerank" name="ranking" checked>&nbsp;Pagerank&nbsp;&nbsp;&nbsp;
            <?php } ?>

            <button type="submit" class="btn btn-primary btn-sm btn-dark">Submit</button>
        </form>
    </div>

    <?php
    $required_fields = array("id", "og_description", "title", "og_url");
    // display results
    if ($results) {
        $total = (int) $results->response->numFound;
        $start = min(1, $total);
        $end = min($limit, $total);
        $my_fields = array();

        $siteMapFile = "URLtoHTML_latimes_news.csv";
        $file_holder = fopen($siteMapFile, 'r');
        $data = fread($file_holder, filesize($siteMapFile));
        $sitemap = array();
        $my_array = explode("\n", $data);
        foreach ($my_array as $line) {
            if ($line == "")
                continue;
            $temp = explode(",", $line);
            $sitemap["/Users/Nandhini/Documents/Courses/CSCI 572 - IR Fall 20/Assignment 4/solr-7.7.0/../LATIMES/latimes/" . $temp[0]] = $temp[1];
        }
        fclose($file_holder);

        $codes = $sitemap;
    ?>
        <!-- <div class="results">Results
            <?php echo $start; ?> - <?php echo $end; ?> of <?php echo $total; ?>:
        </div> -->
        <br>
        <ol>
            <?php
            // iterate result documents
            foreach ($results->response->docs as $doc) {
            ?>
                <li list-group-item-dark>
                    <table class="table table-dark table-striped" style="border: 1px solid black; text-align: left">
                        <?php
                        $url = "~";
                        foreach ($doc as $field => $value) {
                            if ($field == "og_url") $url = $value;
                            if ($field == "id") $file_id = $value;
                            if (in_array($field, $required_fields)) {
                                array_push($my_fields, $field);
                            }
                        }

                        if ($file_id && ($url == "~" || $url == "")) {
                            $url = $codes[$file_id];
                        }
                        // echo "Fields", print_r($my_fields);
                        $diff = array_diff($required_fields, $my_fields);
                        // echo "Diff", print_r($diff);
                        foreach ($diff as $n => $f) {
                            $v = "N/A";
                            $doc->setField($f, $v);
                        }
                        $my_fields = array();

                        // iterate document fields / values
                        foreach ($doc as $field => $value) {
                            if (in_array($field, $required_fields)) {
                        ?>
                                <tbody>
                                    <tr>
                                        <th><?php if (strval($field) == "id") {
                                                $field = "ID";
                                            } else if (strval($field) == "og_url") {
                                                $field = "URL";
                                            } else if (strval($field) == "og_description") {
                                                $field = "Description";
                                            }
                                            echo ucwords(htmlspecialchars($field, ENT_NOQUOTES, 'utf-8'));
                                            ?></th>
                                        <td><?php if (strval($field) == "title") { ?>
                                                <a href=<?php echo $url ?> target="_blank">
                                                    <?php echo htmlspecialchars($value, ENT_NOQUOTES, 'utf-8'); ?>
                                                </a>
                                            <?php
                                            } else if (strval($field) == "URL") {
                                            ?>
                                                <a href=<?php echo $url ?> target="_blank">
                                                    <?php echo $url; ?>
                                                </a>
                                        <?php
                                            } else {
                                                if ($value == "") $value = "N/A";
                                                echo htmlspecialchars($value, ENT_NOQUOTES, 'utf-8');
                                            }
                                        } ?></td>
                                    </tr>
                                </tbody>
                            <?php
                        } ?>
                    </table>
                </li>
            <?php
            }
            ?>
        </ol>
    <?php

    }
    ?>
</body>

</html>