<?php

class DocumentRegistration
{
    private $db;
    private $twig;

    function __construct(PDO $db, $twig)
    {
        $this->db = $db;
        $this->twig = $twig;
    }

    function searchEngine()
    {
        try {
            $searchstring = $_GET['search'];
            $matchingDocuments = array();
            $stmt = $this->db->query("SELECT PK_Document, description FROM CMS_Document");
            $descriptions = $stmt->fetchAll();
            foreach ($descriptions as $des) {
                if (stristr($des['description'], $searchstring)) {
                    $stmt = $this->db->query("SELECT PK_Document, filetype, filename, FK_Catalog 
                                                        FROM CMS_Document WHERE PK_Document = " . $des['PK_Document']);
                    $matchingDocuments[] = $stmt->fetch(PDO::FETCH_ASSOC);
                }
            }
            $stmt = $this->db->query("SELECT * FROM CMS_Catalog WHERE PK_Catalog <> FK_ParentKey");
            $subFolders = $stmt->fetchAll();
            $stmt2 = $this->db->query("SELECT PK_Document, filetype, filename, FK_Catalog FROM CMS_Document");
            $allDoucments = $stmt2->fetchAll();
            $stmt3 = $this->db->query("SELECT *, (SELECT filename FROM CMS_Document WHERE PK_Document = FK_Document) 
                                                    AS filename, (SELECT filetype FROM CMS_Document WHERE PK_Document = FK_Document) 
                                                    AS filetype FROM Tag_has_Document WHERE FK_Tag IN (SELECT PK_Tag 
                                                    FROM CMS_Tag WHERE tagtext = '" . $searchstring . "')");
            if ($stmt3) {
                $byTags = $stmt3->fetchAll();
            } else {
                echo "error";
            }
            echo $this->twig->render('index.twig', array('docs' => $allDoucments, 'folders' => $subFolders, 'byDescription' => $matchingDocuments, 'byTags' => $byTags,
                'link' => $_SERVER['PHP_SELF'], 'search' => $searchstring));
        } catch (Exception $e) {
            print $e->getMessage();
        }
    }

    function deletePost()
    {
        $id = $_GET['id'];
        $stmt = $this->db->query("DELETE FROM CMS_Comment WHERE FK_Document =  " . $id);
        $stmt = $this->db->query("DELETE FROM Tag_has_Document WHERE FK_Document =" . $id);
        $stmt = $this->db->query("DELETE FROM CMS_Document WHERE PK_Document =" . $id);
    }

    function uploadDocument()
    {
        $file = $_FILES['file']['tmp_name'];
        $name = $_POST['name'];
        $type = $_FILES['file']['type'];
        $size = $_FILES['file']['size'];
        $description = $_POST['description'];
        $tags = $_POST['tags'];
        $tagarray = explode(';', $tags);
        $userId = $_SESSION['id'];        // Her må programmet hente id ifra den User som logget inn og som laster opp
        $currCatalog = $_GET['cat'];  // Her må programmet hente katalog-ID for den katalogen som er aktiv(?)
        if (is_uploaded_file($file)) {
            try {
                $data = file_get_contents($file);
                $stmt = $this->db->prepare("    INSERT INTO `CMS_Document` (
                                                                    `filesize`, 
                                                                    `filename`, 
                                                                    `filetype`, 
                                                                    `filecode`, 
                                                                    `dateuploaded`, 
                                                                    `dateedited`, 
                                                                    `description`, 
                                                                    `FK_User`, 
                                                                    `FK_Catalog`
                                                                ) VALUES (
                                                                    :filesize, 
                                                                    :filename, 
                                                                    :filetype, 
                                                                    :filecode, 
                                                                    NOW(), 
                                                                    NOW(), 
                                                                    :description, 
                                                                    :author, 
                                                                    :currCatalog
                                                                )");
                $stmt->bindParam(':filesize', $size, PDO::PARAM_INT);
                $stmt->bindParam(':filename', $name, PDO::PARAM_STR);
                $stmt->bindParam(':filetype', $type, PDO::PARAM_STR);
                $stmt->bindParam(':filecode', $data, PDO::PARAM_LOB);
                $stmt->bindParam(':description', $description, PDO::PARAM_STR);
                $stmt->bindParam(':author', $userId, PDO::PARAM_INT);
                $stmt->bindParam(':currCatalog', $currCatalog, PDO::PARAM_INT);
                $result = $stmt->execute();
                $lastId = $this->db->lastInsertId();

                foreach ($tagarray as $tag) {
                    $stmt2 = $this->db->query("INSERT INTO CMS_Tag (tagtext) VALUES ('" . $tag . "')");
                    $stmt3 = $this->db->query("INSERT INTO Tag_has_Document (FK_Tag, FK_Document) VALUES 
                                                          ((SELECT PK_Tag FROM CMS_Tag WHERE tagtext = '" . $tag . "'), " . $lastId . ")");
                }

                if ($result) {
                    echo "Success";
                } else {
                    echo "Error";
                }
                header("Location: CMS.php?viewId=" . $lastId);
            } catch (Exception $e) {
                print($e->getMessage());
            }
        } else {
            echo "There was a problem";
        }
    }

    function viewDocument($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT filename, filetype, filecode FROM CMS_Document WHERE PK_Document = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            if (!$item = $stmt->fetch(PDO::FETCH_ASSOC)) {
                throw new InvalidArgumentException('Invalid id: ' . $id);
            } else {
                $filename = $item['filename'];
                $filetype = $item['filetype'];
                $filecode = $item['filecode'];

                Header("Content-type: $filetype");
                Header("Content-Disposition: filename=\"$filename\"");
                echo $filecode;
            }
        } catch (Exception $e) {
            print $e->getMessage() . PHP_EOL;
        }
    }

    function viewPost($id)
    {
        $stmt = $this->db->prepare("SELECT FK_User, filename, filetype, filecode, description FROM CMS_Document WHERE PK_Document=:id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $file = $stmt->fetch(PDO::FETCH_ASSOC);

        $result1 = $this->db->query("SELECT FK_Tag, (SELECT tagtext FROM CMS_Tag WHERE FK_Tag = PK_Tag) AS tagtext 
                                                FROM Tag_has_Document WHERE FK_Document = " . $id);
        $tags = $result1->fetchAll();

        $result2 = $this->db->query("SELECT *, (SELECT username FROM CMS_User WHERE PK_User = FK_User) AS username
                FROM CMS_Comment WHERE FK_Document =" . $id);
        $allComments = $result2->fetchAll();

        echo $this->twig->render('view.twig', array('doc' => $file, 'id' => $id, 'link' => $_SERVER['PHP_SELF'],
            'comments' => $allComments, 'tags' => $tags, 'loggedin' => (isset($_SESSION['loggedin']) ? true : false)));
    }

    function viewAll()
    {
        try {
            $currentCatalog = 1;
            if (isset($_GET['cat']))
                $currentCatalog = $_GET['cat'];
            $stmt = $this->db->query("SELECT * FROM CMS_Catalog WHERE FK_ParentKey = " . $currentCatalog . " AND PK_Catalog <> FK_ParentKey");
            $subFolders = $stmt->fetchAll();
            $stmt2 = $this->db->query("SELECT PK_Document, filetype, filename, FK_Catalog FROM CMS_Document WHERE FK_Catalog = " . $currentCatalog);
            $allDoucments = $stmt2->fetchAll();
            echo $this->twig->render('index.twig', array('docs' => $allDoucments, 'folders' => $subFolders,
                'link' => $_SERVER['PHP_SELF'], 'uploadcat' => $currentCatalog, 'supercat' => $this->getSuperCat()));

        } catch (Exception $e) {
            print $e->getMessage();
        }
    }

    function addFolder()
    {
        try {
            $name = $_POST['foldername'];
            if (isset($_GET['cat']))
                $curcat = $_GET['cat'];
            else
                $curcat = 1;
            $stmt = $this->db->query("INSERT INTO CMS_Catalog (catalogname, FK_ParentKey) VALUES ('" . $name . "', " . $curcat . ")");
            header("Location: CMS.php?cat=" . $curcat);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    function addComment()
    {
        try {
            if (!empty($_SESSION['id'])) {
                $comment = $_POST['comment'];
                $fk_user = $_SESSION['id'];
                $fk_document = $_GET['viewId'];
                $stmt = $this->db->prepare("INSERT INTO CMS_Comment (
                                                        text,
                                                        datecreated,
                                                        FK_Document,
                                                        FK_User
                                                    ) VALUES ( 
                                                         :text,
                                                          NOW(), 
                                                         :FK_Document,
                                                         :FK_User
                                                         )");
                $stmt->bindParam(':FK_Document', $fk_document, PDO::PARAM_STR);
                $stmt->bindParam(':FK_User', $fk_user, PDO::PARAM_STR);
                $stmt->bindParam(':text', $comment, PDO::PARAM_STR);
                $result = $stmt->execute();
                if ($result) {
                } else {
                    echo "error";
                }
            }
        } catch
        (Exception $e) {
            echo $e->getMessage();
        }
    }

    function editComment()
    {

    }

    function deleteComment()
    {
        if ($_POST['username'] == $_SESSION['username']) {
            $PK_Comment = $_POST["delete"];
            $id = $_SESSION['id'];
            $stmt = $this->db->query("DELETE FROM CMS_Comment WHERE PK_Comment =  " . $PK_Comment);
        } else if ($_SESSION['user_type'] = 'admin') {
            $PK_Comment = $_POST["delete"];
            $id = $_SESSION['id'];
            $stmt = $this->db->query("DELETE FROM CMS_Comment WHERE PK_Comment =  " . $PK_Comment);
            echo "Admin delete";
        } else {
            echo "cannot delete others comments";
        }

    }

    function getSuperCat(): int
    {
        try {
            if (isset($_GET['cat'])) {
                $curcat = $_GET['cat'];
                $stmt = $this->db->query("SELECT FK_ParentKey FROM CMS_Catalog WHERE PK_Catalog = " . $curcat);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $rval = $result['FK_ParentKey'];
            } else {
                $rval = 1;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        return $rval;
    }
}
