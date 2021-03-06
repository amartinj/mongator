<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Group;

use Mongator\Archive;
use Mongator\Document\Document;

/**
 * EmbeddedGroup.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
class EmbeddedGroup extends Group
{
    /**
     * Set the root and path of the embedded group.
     *
     * @param \Mongator\Document\Document $root The root document.
     * @param string                      $path The path.
     *
     * @api
     */
    public function setRootAndPath(Document $root, $path)
    {
        Archive::set($this, 'root_and_path', array('root' => $root, 'path' => $path));

        foreach ($this->getAdd() as $key => $document) {
            $document->setRootAndPath($root, $path.'._add'.$key);
        }
    }

    /**
     * Returns the root and the path.
     *
     * @api
     */
    public function getRootAndPath()
    {
        return Archive::getOrDefault($this, 'root_and_path', null);
    }

    /**
     * {@inheritdoc}
     */
    public function add($documents)
    {
        parent::add($documents);

        if ($rap = $this->getRootAndPath()) {
            foreach ($this->getAdd() as $key => $document) {
                $document->setRootAndPath($rap['root'], $rap['path'].'._add'.$key);
            }
        }
    }

    /**
     * Set the saved data.
     *
     * @param array $data The saved data.
     */
    public function setSavedData(array $data)
    {
        Archive::set($this, 'saved_data', $data);
    }

    /**
     * Returns the saved data.
     *
     * @return array|null The saved data or null if it does not exist.
     */
    public function getSavedData()
    {
        return Archive::getOrDefault($this, 'saved_data', null);
    }

    /**
     * {@inheritdoc}
     */
    protected function doInitializeSavedData()
    {
        $rap = $this->getRootAndPath();
        $rap['root']->addFieldCache($rap['path']);

        $data = $this->getSavedData();
        if ($data !== null) {
            return $data;
        }

        return array();
    }

    /**
     * {@inheritdoc}
     */
    protected function doInitializeSaved(array $data)
    {
        $documentClass = $this->getDocumentClass();
        $rap = $this->getRootAndPath();
        $mongator = $rap['root']->getMongator();

        $saved = array();
        foreach ($data as $key => $datum) {
            if ( $datum === null ) continue;

            $saved[] = $document = $mongator->create($documentClass);
            $document->setDocumentData($datum);
            $document->setRootAndPath($rap['root'], $rap['path'].'.'.$key);
        }

        return $saved;
    }
}
