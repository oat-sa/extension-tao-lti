<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoLti\test\unit\models\classes\LtiProvider;

use oat\generis\test\TestCase;
use oat\taoLti\models\classes\LtiProvider\LtiProviderRepositoryInterface;
use oat\taoLti\models\classes\LtiProvider\LtiProviderService;

class LtiProviderServiceTest extends TestCase
{
    const COUNT_1 = 10;
    const COUNT_2 = 12;
    const FIND_ALL_1 = ['key1' => 'value1'];
    const FIND_ALL_2 = ['key2' => 'value2'];
    const LABEL = 'the sought label';
    const ID = 'uri';
    const OAUTH_KEY = 'okey1';
    const SEARCH_1 = ['key3' => 'value3'];
    const SEARCH_2 = ['key4' => 'value4'];
    const SEARCH_ID_RESULT = ['uri' => 'v4'];
    const SEARCH_OAUTH_KEY_RESULT = ['uri' => 'v5'];

    /** @var LtiProviderService */
    private $subject;

    public function setUp(): void
    {
        $repository1 = $this->createRepositoryMock(
            self::COUNT_1,
            self::FIND_ALL_1,
            self::LABEL,
            self::SEARCH_1,
            self::ID,
            self::OAUTH_KEY,
            self::SEARCH_ID_RESULT,
            null
        );
        $repository2 = $this->createRepositoryMock(
            self::COUNT_2,
            self::FIND_ALL_2,
            self::LABEL,
            self::SEARCH_2,
            self::ID,
            self::OAUTH_KEY,
            null,
            self::SEARCH_OAUTH_KEY_RESULT
        );

        $this->subject = new LtiProviderService([
            LtiProviderService::LTI_PROVIDER_LIST_IMPLEMENTATIONS => [$repository1, $repository2],
        ]);
    }

    public function testCount()
    {
        $this->assertEquals(self::COUNT_1 + self::COUNT_2, $this->subject->count());
    }

    public function testFindAll()
    {
        $this->assertEquals(array_merge(self::FIND_ALL_1, self::FIND_ALL_2), $this->subject->findAll());
    }

    public function testSearchByLabel()
    {
        $this->assertEquals(array_merge(self::SEARCH_1, self::SEARCH_2), $this->subject->searchByLabel(self::LABEL));
    }

    public function testSearchById()
    {
        $this->assertEquals(self::SEARCH_ID_RESULT, $this->subject->searchById(self::ID));
    }

    public function testSearchByOauthKey()
    {
        $this->assertEquals(self::SEARCH_OAUTH_KEY_RESULT, $this->subject->searchByOauthKey(self::OAUTH_KEY));
    }

    private function createRepositoryMock(
        $count,
        array $findAllResult,
        $label,
        array $searchResult,
        $searchId,
        $searchOauthKey,
        $searchByIdhResult,
        $searchByOauthKeyResult
    ) {
        $repository = $this->getMockBuilder(LtiProviderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['count', 'findAll', 'searchByLabel', 'searchById'])
            ->getMockForAbstractClass();
        $repository->method('count')->willReturn($count);
        $repository->method('findAll')->willReturn($findAllResult);
        $repository->method('searchByLabel')->with($label)->willReturn($searchResult);
        $repository->method('searchById')->with($searchId)->willReturn($searchByIdhResult);
        $repository->method('searchByOauthKey')->with($searchOauthKey)->willReturn($searchByOauthKeyResult);

        return $repository;
    }
}
