<?php
/** 
 * Blog_Class small Info 
 * 
 * Blog_Class longer info 
 * over multiple lines 
 * to describe everything. 
 * 
 * Example usage: 
 * if (Example_Class::example()) { 
 *      print "I am an example."; 
 * } 
 * 
 * @package Name 
 * @author  First Author <mail1@example.com> 
 * @author  Second Author <mail2@example.com>  
 * @version $Revision: 1.3 $ 
 * @access  public 
 * @see     http://www.example.com/references 
*/
class Article {

    /* ------------- PRIVATE PARAMS ------------- */
    private $db;
    
    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "article";
    private $t_preview = "article_preview";
    private $v_preview = "v_article_preview";
    private $v_edit = "v_article_edit";
    /* ----------- PUBLIC BASIC PARAMS ---------- */
    public $id;
    public $user_id;

    public $url;
    public $title;
    public $keywords;
    public $content;
    public $language;
    
    public $color;
    public $dark;
    public $description;
    public $img_url;
    public $img_lazy;
    public $img_phrase;

    public $publication_date;
    public $creation_stamp;
    public $update_stamp;
    
    /* ------------------ INIT ------------------ */
    public function __construct($db) { 
        $this->db = $db;
    }

    /* ----------------- METHODS ---------------- */

    public function readPreview() {

        $stmt = $this->db->conn->prepare("
            SELECT * FROM ".$this->v_preview . " WHERE 
            `publication_date` <= :publication_date
        ");

        $this->db->bind($stmt, 
            ['publication_date'], [$this->publication_date]
        );

        $this->db->execute($stmt);

        $entries = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($entries, $this->formPreviewObject($row));
        }

        return $entries;

    }

    public function readArticle() {

        $stmt = $this->db->conn->prepare("
            SELECT * FROM ".$this->t_main . " WHERE 
            `publication_date` <= :publication_date AND 
            `url` = :url
        ");
        $this->db->bind($stmt, 
            ['publication_date', 'url'],
            [$this->publication_date, $this->url]
        );
        $this->db->execute($stmt);

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $this->formArticleObject($row);
        }

        throw new Exception('no_entry', 404);

        
    }

    public function readEdit() {

        if($this->url) {
            $stmt = $this->db->conn->prepare("
            SELECT * FROM ".$this->v_edit." 
            WHERE `url` = :url
        ");
        } else {
            $stmt = $this->db->conn->prepare("
            SELECT url, title, publication_date FROM ".$this->v_edit."
        ");
        }

        if($this->url) {
            $this->db->bind($stmt, ['url'], [$this->url]);
        }
        $this->db->execute($stmt);

        if ($this->url && $stmt->rowCount() < 1) {
            throw new Exception('entry_not_found', 404);
        }

        if($this->url){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $this->formEditObject($row);
        }

        $entries = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($entries, $this->formEditObject($row));
        }

        return $entries;

    }


    public function create() {

        $stmt = $this->db->conn->prepare("
            INSERT INTO ".$this->t_main . " (
                `user_id`, `url`, `title`, `keywords`, 
                `content`, `language`, `publication_date`
            ) VALUES (
                :user_id, :url, :title, :keywords, 
                :content, :language, :publication_date
            );
        ");

        $this->db->bind($stmt, [
            'user_id', 'url', 'title', 'keywords', 
            'content', 'language', 'publication_date'
        ], [
            $this->user_id, $this->url, $this->title, $this->keywords, 
            $this->content, $this->language, $this->publication_date
        ]);

        $this->db->execute($stmt);

        $this->id = $this->db->conn->lastInsertId();

        $stmt = $this->db->conn->prepare("
            INSERT INTO ".$this->t_preview . " (
                `article_id`, `color`, `dark`, `description`, 
                `img_url`, `img_lazy`, `img_phrase`
            ) VALUES (
                :article_id, :color, :dark, :description, 
                :img_url, :img_lazy, :img_phrase
            );
        ");

        $this->db->bind($stmt, [
            'article_id', 'color', 'dark', 'description', 
            'img_url', 'img_lazy', 'img_phrase'
        ], [
            $this->id, $this->color, $this->dark, $this->description, 
            $this->img_url, $this->img_lazy, $this->img_phrase
        ]);

        $this->db->execute($stmt);

    }

    public function delete() {
        
        // TODO

    }

    public function edit() {


        $stmt = $this->db->conn->prepare("
            SELECT * FROM ".$this->t_main . " WHERE
            `url` = :url
        ");
        $this->db->bind($stmt, 
            ['url'],
            [$this->url]
        );
        $this->db->execute($stmt);
        $this->id = ($stmt->fetch(PDO::FETCH_ASSOC))['id'];

        $stmt = $this->db->conn->prepare("
            UPDATE ".$this->t_main . " SET 
            `title` = :title,
            `keywords` = :keywords,
            `content` = :content,
            `language` = :language,
            `publication_date` = :publication_date
            WHERE `id` = :id ;
        ");

        $this->db->bind($stmt, [
            'id', 'title', 'keywords', 
            'content', 'language', 'publication_date'
        ], [
            $this->id, $this->title, $this->keywords, 
            $this->content, $this->language, $this->publication_date
        ]);

        $this->db->execute($stmt);

        $stmt = $this->db->conn->prepare("
            UPDATE ".$this->t_preview . " SET 
            `color` = :color,
            `dark` = :dark,
            `description` = :description,
            `img_url` = :img_url,
            `img_lazy` = :img_lazy,
            `img_phrase` = :img_phrase
            WHERE `article_id` = :article_id ;
        ");

        $this->db->bind($stmt, [
            'article_id', 'color', 'dark', 'description', 
            'img_url', 'img_lazy', 'img_phrase'
        ], [
            $this->id, $this->color, $this->dark, $this->description, 
            $this->img_url, $this->img_lazy, $this->img_phrase
        ]);

        $this->db->execute($stmt);

    }


    public function formArticleObject($obj = false) {

        if(!$obj) {
            $obj = (array) $this;
        }

        $object = [
            "url" => $obj['url'],
            "title" => $obj['title'],
            "keywords" => (bool) $obj['keywords'],
            "language" => $obj['language'],
            "content" => $obj['content'],
            "publicationDate" => $obj['publication_date']
        ];

        if (isset($obj['keywords'])) {
            $arr = explode(", ", $obj['keywords']);
            $object['keywords'] = $arr;
        }

        return $object;

    }

    public function formPreviewObject($obj = false) {

        
        if(!$obj) {
            $obj = (array) $this;
        }

        $object = [
            "url" => $obj['url'],
            "title" => $obj['title'],
            "keywords" => (bool) $obj['keywords'],
            "language" => $obj['language'],
            "publicationDate" => $obj['publication_date'],
            "imgUrl" => $obj['img_url'],
            "imgLazy" => $obj['img_lazy'],
            "imgPhrase" => $obj['img_phrase'],
            "description" => $obj['description'],
            "design" => [
                "color" => $obj['color'],
                "dark" => (bool) $obj['dark']
            ]
        ];

        if (isset($obj['keywords'])) {
            $arr = explode(", ", $obj['keywords']);
            $object['keywords'] = $arr;
        }

        return $object;

    }

    public function formEditObject($obj = false) {

        if(!$obj) {
            $obj = (array) $this;
        }

        if(!isset($obj["content"])) {
            $object = [
            "url" => $obj['url'],
            "title" => $obj['title'],
            "publicationDate" => $obj['publication_date']
        ];
        } else {
            $object = [
            "url" => $obj['url'],
            "title" => $obj['title'],
            "keywords" => (bool) $obj['keywords'],
            "language" => $obj['language'],
            "content" => $obj['content'],
            "description" => $obj['description'],
            "publicationDate" => $obj['publication_date'],
            "imgUrl" => $obj['img_url'],
            "imgLazy" => $obj['img_lazy'],
            "imgPhrase" => $obj['img_phrase'],
            "design" => [
                "color" => $obj['color'],
                "dark" => (bool) $obj['dark']
            ]
        ];
        }

        if (isset($obj['keywords'])) {
            $arr = explode(", ", $obj['keywords']);
            $object['keywords'] = $arr;
        }

        return $object;
        
    }
    
}