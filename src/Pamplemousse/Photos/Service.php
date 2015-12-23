<?php

namespace Pamplemousse\Photos;

class Service
{
    protected $app;
    protected $config;
    protected $conn;

    public function __construct($app)
    {
        $this->app = $app;
        $this->config = $app['config'];
        $this->conn = $app['db'];
    }

    public function add($filepath)
    {
        $image = $this->app['imagine']->open(__DIR__. '/../../../web/'. $filepath);
        $metadata = $image->metadata();
        $this->conn->insert('pamplemousse__item', [
            'path' => $filepath,
            'date_taken' => $metadata["exif.DateTimeOriginal"],
        ]);
    }

    public function getPhotos()
    {
        $items = $this->conn->fetchAll('SELECT * FROM pamplemousse__item WHERE type = ? ORDER BY date_taken DESC', array('picture'));
        $photos = [];
        foreach ($items as $id => $item) {
            $photos[] = $this->itemToPhoto($id, $item);
        }

        return $photos;
    }

    public function getPhoto($id)
    {

        $item = $this->conn->fetchAssoc('SELECT * FROM pamplemousse__item WHERE id = ?', array($id));
        return $this->itemToPhoto($id, $item);
    }

    protected function itemToPhoto($id, $item)
    {
        return [
            'id' => $id,
            'url' => $item['path'],
            'filename' => basename($item['path'])
        ];
    }
}
