<?php
/**
 * Copyright © PassKeeper, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PassKeeper\CmsUrlRewrite\Observer;

use PassKeeper\Framework\Event\Observer as EventObserver;
use PassKeeper\UrlRewrite\Model\UrlPersistInterface;
use PassKeeper\Framework\Event\ObserverInterface;
use PassKeeper\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator;
use PassKeeper\UrlRewrite\Service\V1\Data\UrlRewrite;

class ProcessUrlRewriteSavingObserver implements ObserverInterface
{
    /**
     * @var \PassKeeper\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator
     */
    protected $cmsPageUrlRewriteGenerator;

    /**
     * @var UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * @param \PassKeeper\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator $cmsPageUrlRewriteGenerator
     * @param UrlPersistInterface $urlPersist
     */
    public function __construct(CmsPageUrlRewriteGenerator $cmsPageUrlRewriteGenerator, UrlPersistInterface $urlPersist)
    {
        $this->cmsPageUrlRewriteGenerator = $cmsPageUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
    }

    /**
     * Generate urls for UrlRewrite and save it in storage
     *
     * @param \PassKeeper\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var $cmsPage \PassKeeper\Cms\Model\Page */
        $cmsPage = $observer->getEvent()->getObject();

        if ($cmsPage->dataHasChangedFor('identifier')
            || $cmsPage->dataHasChangedFor('store_id')
            || $cmsPage->getData('rewrites_update_force')
        ) {
            $urls = $this->cmsPageUrlRewriteGenerator->generate($cmsPage);

            $this->urlPersist->deleteByData([
                UrlRewrite::ENTITY_ID => $cmsPage->getId(),
                UrlRewrite::ENTITY_TYPE => CmsPageUrlRewriteGenerator::ENTITY_TYPE,
            ]);
            $this->urlPersist->replace($urls);
        }
    }
}
