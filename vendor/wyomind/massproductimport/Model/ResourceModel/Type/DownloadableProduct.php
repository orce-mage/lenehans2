<?php

namespace Wyomind\MassProductImport\Model\ResourceModel\Type;

class DownloadableProduct extends \Wyomind\MassProductImport\Model\ResourceModel\Type\AbstractResource
{

    const LABEL_SEPARATOR = "|";

    public function _construct()
    {
        $this->tableLink = $this->getTable("downloadable_link");
        $this->tableLinkTitle = $this->getTable("downloadable_link_title");
        $this->tableSample = $this->getTable("downloadable_sample");
        $this->tableSampleTitle = $this->getTable("downloadable_sample_title");
        $this->tableCatalogEntities = $this->getTable("catalog_product_entity_varchar");
        parent::_construct();
    }

    function collect($productId, $value, $strategy, $profile)
    {
        if ($strategy["option"][0] == "link" || $strategy["option"][0] == "sample") {
            // Data:[URL]|[NAME]
            // We recover the different elements of the value
            $part = explode(self::LABEL_SEPARATOR, $value);
            $link = trim($part[0]);
            $title = isset($part[1]) ? $part[1] : null;
            $type = 'file';
            // We check if file is a remote url or a path
            $pattern = '#^(http|https|ftp|sftp):\/\/#';
            preg_match($pattern, $link, $matches, PREG_OFFSET_CAPTURE);
            if (count($matches) > 0) {
                $type = 'url';
            }
        }
        if ($link && $strategy["option"][0] == "link") {
            // Link requests
            // We delete entries from the table (useful in case of product update)
            $this->queries[$this->queryIndexer][] = "DELETE FROM `" . $this->tableLink . "` WHERE product_id=" . $productId . ";";
            // We then create the entries in the different tables
            if ($type == 'url') {
                $this->queries[$this->queryIndexer][] = "INSERT INTO `" . $this->tableLink . "` (product_id, link_url, link_type) values(" . $productId . " , '" . $link . "' , '" . $type . "');";
            } else {
                $this->queries[$this->queryIndexer][] = "INSERT INTO `" . $this->tableLink . "` (product_id, link_file, link_type) values(" . $productId . " , '" . $link . "' , '" . $type . "');";
            }
            $this->queries[$this->queryIndexer][] = "SELECT @value_id:= LAST_INSERT_ID();";
            foreach ($strategy['storeviews'] as $storeview) {
                $this->queries[$this->queryIndexer][] = "INSERT INTO `" . $this->tableLinkTitle . "` (link_id, title, store_id) values(@value_id ,  '" . $title . "', '" . $storeview . "');";
            }
        } elseif ($link && $strategy["option"][0] == "sample") {
            // Sample requests
            // We delete entries from the table (useful in case of product update)
            $this->queries[$this->queryIndexer][] = "DELETE FROM `" . $this->tableSample . "` WHERE product_id=" . $productId . ";";
            // We then create the entries in the different tables
            if ($type == 'url') {
                $this->queries[$this->queryIndexer][] = "INSERT INTO `" . $this->tableSample . "` (product_id, sample_url, sample_type) values(" . $productId . " , '" . $link . "' , '" . $type . "');";
            } else {
                $this->queries[$this->queryIndexer][] = "INSERT INTO `" . $this->tableSample . "` (product_id, sample_file, sample_type) values(" . $productId . " , '" . $link . "' , '" . $type . "');";
            }
            $this->queries[$this->queryIndexer][] = "SELECT @value_id:= LAST_INSERT_ID();";
            foreach ($strategy['storeviews'] as $storeview) {
                $this->queries[$this->queryIndexer][] = "INSERT INTO `" . $this->tableSampleTitle . "` (sample_id, title, store_id) values(@value_id ,  '" . $title . "', '" . $storeview . "');";
            }
        }
        parent::collect($productId, $value, $strategy, $profile);
    }

    public function getDropdown()
    {

        $i = 0;
        $dropdown = [];
        // Attributes
        $fields = ["attribute_code"];
        $conditions = [
            ["in" =>
                [
                    "links_title",
                    "samples_title"
                ]
            ],
        ];
        $linkList = $this->getAttributesList($fields, $conditions, false);
        foreach ($linkList as $attribute) {
            if (!empty($attribute['frontend_label'])) {
                $dropdown['Downloadable Products'][$i]['label'] = $attribute['frontend_label'];
                $dropdown['Downloadable Products'][$i]["id"] = "Attribute/" . $attribute['backend_type'] . "/" . $attribute['attribute_id'];
                $dropdown['Downloadable Products'][$i]['style'] = "downloadable-product storeviews-dependent no-configurable";
                $dropdown['Downloadable Products'][$i]['type'] = "Title of the downloadable resource";
                $dropdown['Downloadable Products'][$i]['value'] = "";
                $i++;
            }
        }
        // Others
        $dropdown['Downloadable Products'][$i]['label'] = __("Link urls");
        $dropdown['Downloadable Products'][$i]["id"] = "DownloadableProduct/link";
        $dropdown['Downloadable Products'][$i]['style'] = "downloadable-product storeviews-dependent no-configurable";
        $dropdown['Downloadable Products'][$i]['type'] = "Link to the file (relative or absolute path) and optional file's name separated " . self::LABEL_SEPARATOR;
        $dropdown['Downloadable Products'][$i]['value'] = "http://www.example.com/filename.ext " . self::LABEL_SEPARATOR . " downloadable file name";
        $i++;
        $dropdown['Downloadable Products'][$i]['label'] = __("Sample urls");
        $dropdown['Downloadable Products'][$i]["id"] = "DownloadableProduct/sample";
        $dropdown['Downloadable Products'][$i]['style'] = "downloadable-product storeviews-dependent no-configurable";
        $dropdown['Downloadable Products'][$i]['type'] = "Link to the file (relative or absolute path) and optional file's name separated " . self::LABEL_SEPARATOR;
        $dropdown['Downloadable Products'][$i]['value'] = "http://www.example.com/filename.ext " . self::LABEL_SEPARATOR . " downloadable file name";


        return $dropdown;
    }
}
