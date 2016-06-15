<?php

include 'functions.php';
include 'mysqlConnect.php';

class grid {

    public $id, $file_src, $shape, $title, $sub_title, $url, $type;

    public function __construct($id) {
        $this->id = $id;
        $this->file_src = grid_search($id, 'file_src');
        $this->shape = grid_search($id, 'shape');
        $this->title = grid_search($id, 'title');
        $this->sub_title = grid_search($id, 'sub_title');
        $this->url = grid_search($id, 'url');
        $this->type = grid_search($id, 'type');
    }

    public function toString() {
        $toRet = '<li><div style="float:left; width:80px;" align="center">';
        if ($this->type != 'video') {
            $toRet .= '<img src="grid/images/' . $this->file_src . '" width="80px">';
        } else {
            $toRet .= 'Tis a video, sire...';
        }
        $toRet .= '</div>'
                . '<div style="float:left;" align="left">'
                . '<strong>' . $this->title . '</strong> - ' . $this->sub_title . '<br/>'
                . '' . ($this->type != 'hidden' ? '<a href="?id='.$this->id.'&setType=hidden&grid">hide</a>' : '<a href="?id='.$this->id.'&setType=visible&grid">unhide</a>') . ' &bull; <a href="?id='.$this->id.'&delete&grid">delete</a>'
                . '</div>'
                . '</li>';

        return $toRet;
    }

    public function updateDB($what, $value) {
        $sql = "UPDATE grid SET " . $what . "=\"" . $value . "\" WHERE id=" . $this->id . ";";
        mysql_query($sql) or die(mysql_error());
    }

    public function setFile_src($file_src) {
        $this->updateDB('file_src', $file_src);
        $this->file_src = $file_src;
    }

    function delete() {
        $sql = 'DELETE FROM grid WHERE id="' . $this->id . '";';
        mysql_query($sql) or die(mysql_error());
    }

    public function setShape($shape) {
        $this->updateDB('shape', $shape);
        $this->shape = $shape;
    }

    public function setTitle($title) {
        $this->updateDB('type', $title);
        $this->title = $title;
    }

    public function setSub_title($sub_title) {
        $this->updateDB('sub_title', $sub_title);
        $this->sub_title = $sub_title;
    }

    public function setUrl($url) {
        $this->updateDB('url', $url);
        $this->url = $url;
    }

    public function setType($type) {
        if ($type != 'hidden') {
            $imgExts = array("gif", "jpg", "jpeg", "png", "tiff", "tif");
            $urlExt = pathinfo(substr($this->file_src, -6), PATHINFO_EXTENSION);
            if (in_array($urlExt, $imgExts)) {
                $type = 'image';
            } else {
                $type = 'video';
            }
        }
        $this->updateDB('type', $type);
        $this->type = $type;
    }
    
    public function functionRoute($name, $value=''){
        $this->$name($value);
    }
}
