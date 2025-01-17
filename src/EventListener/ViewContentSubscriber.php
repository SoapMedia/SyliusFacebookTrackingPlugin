<?php

declare(strict_types=1);

namespace Setono\SyliusFacebookTrackingPlugin\EventListener;

use Setono\SyliusFacebookTrackingPlugin\Builder\ContentBuilder;
use Setono\SyliusFacebookTrackingPlugin\Builder\ViewContentBuilder;
use Setono\SyliusFacebookTrackingPlugin\Event\BuilderEvent;
use Setono\SyliusFacebookTrackingPlugin\Tag\FbqTag;
use Setono\SyliusFacebookTrackingPlugin\Tag\FbqTagInterface;
use Setono\SyliusFacebookTrackingPlugin\Tag\Tags;
use Setono\TagBagBundle\TagBag\TagBagInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Component\Product\Model\ProductInterface;

final class ViewContentSubscriber extends TagSubscriber
{
    public static function getSubscribedEvents(): array
    {
        return [
            'sylius.product.show' => [
                'track',
            ],
        ];
    }

    public function track(ResourceControllerEvent $event): void
    {
        if (!$this->hasPixels()) {
            return;
        }

        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        $builder = ViewContentBuilder::create()
            ->setContentName($product->getName())
            ->setContentType(ViewContentBuilder::CONTENT_TYPE_PRODUCT)
            ->addContentId($product->getCode())
        ;

        $contentBuilder = ContentBuilder::create()
            ->setId($product->getCode())
            ->setQuantity(1)
        ;

        $this->dispatch(ContentBuilder::EVENT_NAME, new BuilderEvent($contentBuilder, $product));

        $builder->addContent($contentBuilder);

        $this->dispatch(ViewContentBuilder::EVENT_NAME, new BuilderEvent($builder, $product));

        $this->tagBag->add(new FbqTag(
            Tags::TAG_VIEW_CONTENT,
            FbqTagInterface::EVENT_VIEW_CONTENT,
            $builder
        ), TagBagInterface::SECTION_BODY_END);
    }
}
