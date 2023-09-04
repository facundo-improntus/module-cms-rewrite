<?php
/**
 * Copyright © PassKeeper, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PassKeeper\CmsUrlRewrite\Plugin\Cms\Model\ResourceModel;

use PassKeeper\UrlRewrite\Model\UrlPersistInterface;
use PassKeeper\CmsUrlRewrite\Model\CmsPageUrlPathGenerator;
use PassKeeper\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator;
use PassKeeper\UrlRewrite\Service\V1\Data\UrlRewrite;
use PassKeeper\Framework\Model\AbstractModel;
use PassKeeper\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Before save and around delete plugin for \PassKeeper\Cms\Model\ResourceModel\Page:
 * - autogenerates url_key if the merchant didn't fill this field
 * - remove all url rewrites for cms page on delete
 */
class Page
{
    /**
     * @var \PassKeeper\CmsUrlRewrite\Model\CmsPageUrlPathGenerator
     */
    protected $cmsPageUrlPathGenerator;

    /**
     * @var UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * @param CmsPageUrlPathGenerator $cmsPageUrlPathGenerator
     * @param UrlPersistInterface $urlPersist
     */
    public function __construct(
        CmsPageUrlPathGenerator $cmsPageUrlPathGenerator,
        UrlPersistInterface $urlPersist
    ) {
        $this->cmsPageUrlPathGenerator = $cmsPageUrlPathGenerator;
        $this->urlPersist = $urlPersist;
    }

    /**
     * Before save handler
     *
     * @param \PassKeeper\Cms\Model\ResourceModel\Page $subject
     * @param \PassKeeper\Framework\Model\AbstractModel $object
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        \PassKeeper\Cms\Model\ResourceModel\Page $subject,
        \PassKeeper\Framework\Model\AbstractModel $object
    ) {
        /** @var $object \PassKeeper\Cms\Model\Page */
        $urlKey = $object->getData('identifier');
        if ($urlKey === '' || $urlKey === null) {
            $object->setData('identifier', $this->cmsPageUrlPathGenerator->generateUrlKey($object));
        }
    }

    /**
     * On delete handler to remove related url rewrites
     *
     * @param \PassKeeper\Cms\Model\ResourceModel\Page $subject
     * @param AbstractDb $result
     * @param AbstractModel $page
     * @return AbstractDb
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(
        \PassKeeper\Cms\Model\ResourceModel\Page $subject,
        AbstractDb $result,
        AbstractModel $page
    ) {
        if ($page->isDeleted()) {
            $this->urlPersist->deleteByData(
                [
                    UrlRewrite::ENTITY_ID => $page->getId(),
                    UrlRewrite::ENTITY_TYPE => CmsPageUrlRewriteGenerator::ENTITY_TYPE,
                ]
            );
        }

        return $result;
    }
}
