<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Functional\Content;

use ONGR\ElasticsearchBundle\Service\Manager;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;
use Sulu\Component\Content\Compat\PropertyParameter;
use Sulu\Component\SmartContent\DataProviderInterface;
use Sulu\Component\SmartContent\DataProviderResult;

class ArticleDataProviderTest extends SuluTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->initPhpcr();

        /** @var Manager $manager */
        $manager = $this->getContainer()->get('es.manager.default');
        $manager->dropAndCreateIndex();
        $manager = $this->getContainer()->get('es.manager.live');
        $manager->dropAndCreateIndex();
    }

    public function testResolveDataItems()
    {
        $item = $this->createArticle();

        /** @var DataProviderInterface $dataProvider */
        $dataProvider = $this->getContainer()->get('sulu_article.content.data_provider');

        $result = $dataProvider->resolveDataItems([], []);

        $this->assertInstanceOf(DataProviderResult::class, $result);
        $this->assertCount(1, $result->getItems());
        $this->assertEquals($item['id'], $result->getItems()[0]->getId());
    }

    public function testResolveDataItemsTypeParam()
    {
        $this->createArticle();
        $item = $this->createArticle('Test', 'simple');

        /** @var DataProviderInterface $dataProvider */
        $dataProvider = $this->getContainer()->get('sulu_article.content.data_provider');

        $result = $dataProvider->resolveDataItems([], ['type' => new PropertyParameter('type', 'video')]);

        $this->assertInstanceOf(DataProviderResult::class, $result);
        $this->assertCount(1, $result->getItems());
        $this->assertEquals($item['id'], $result->getItems()[0]->getId());
    }

    private function createArticle($title = 'Test-Article', $template = 'default')
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/articles?locale=de&action=publish',
            ['title' => $title, 'template' => $template]
        );

        return json_decode($client->getResponse()->getContent(), true);
    }
}
