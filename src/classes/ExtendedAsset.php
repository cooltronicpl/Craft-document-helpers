<?php

/**
 *
 * Class ExtendedAsset
 *
 * PDF Generator plugin for Craft CMS 3 or Craft CMS 4.
 *
 * @link      https://cooltronic.pl
 * @link      https://potacki.com
 * @license   https://github.com/cooltronicpl/Craft-document-helpers/blob/master/LICENSE.md
 * @copyright Copyright (c) 2023 CoolTRONIC.pl sp. z o.o. by Pawel Potacki
 */

 namespace cooltronicpl\documenthelpers\classes;

 use Craft;
 
 /**
  * @author    CoolTRONIC.pl sp. z o.o. <github@cooltronic.pl>
  * @author    Pawel Potacki
  * @since     1.2.2
  * @since     0.3.2
  */
class ExtendedAsset extends craft\elements\Asset {
    public $assetThumb;
    private $_url;
    private $_isFolder;
    private $_isDraft;
    private $_isRevision;
    private $_isUnpublishedDraft;
    private $_ref;
    private $_status;
    private $_extension;
    private $_hasFocalPoint;
    private $_mimeType;
    private $_path;
    private $_isFolder;
    
    public function getUrl($transform = null, ?bool $immediately = null): ?string
    {
        return $this->_url ?? parent::getUrl();
    }

    public function setUrl($url) {
        $this->_url = $url;
    }

    public function getIsFolder() {
        return $this->_isFolder ?? parent::getIsFolder();
    }

    public function setIsFolder($isFolder) {
        $this->_isFolder = $isFolder;
    }

    public function getIsDraft(): bool {
        return $this->_isDraft ?? parent::getIsDraft();
    }

    public function setIsDraft($isDraft) {
        $this->_isDraft = $isDraft;
    }

    public function getIsRevision(): bool {
        return $this->_isRevision ?? parent::getIsRevision();
    }

    public function setIsRevision($isRevision) {
        $this->_isRevision = $isRevision;
    }

    public function getIsUnpublishedDraft(): bool {
        return $this->_isUnpublishedDraft ?? parent::getIsUnpublishedDraft();
    }

    public function setIsUnpublishedDraft($isUnpublishedDraft) {
        $this->_isUnpublishedDraft = $isUnpublishedDraft;
    }

    public function getRef(): string {
        return $this->_ref ?? parent::getRef();
    }

    public function setRef($ref) {
        $this->_ref = $ref;
    }

    public function getStatus(): string {
        return $this->_status ?? parent::getStatus();
    }

    public function setStatus($status) {
        $this->_status = $status;
    }

    public function getExtension(): string {
        return $this->_extension ?? parent::getExtension();
    }

    public function setExtension($extension) {
        $this->_extension = $extension;
    }

    public function getHasFocalPoint(): bool {
        return $this->_hasFocalPoint ?? parent::getHasFocalPoint();
    }

    public function setHasFocalPoint($hasFocalPoint) {
        $this->_hasFocalPoint = $hasFocalPoint;
    }

    public function getMimeType(): string {
        return $this->_mimeType ?? parent::getMimeType();
    }

    public function setMimeType($mimeType) {
        $this->_mimeType = $mimeType;
    }

    public function getPath($filename = null): string {
        return $this->_path ?? parent::getPath();
    }

    public function setPath($path) {
        $this->_path = $path;
    }

    public function setIsFolder($isFolder) {
        $this->_isFolder = $isFolder;
    }

    public function getIsFolder(): bool {
        return $this->_isFolder ?? parent::getIsFolder();
    }

}