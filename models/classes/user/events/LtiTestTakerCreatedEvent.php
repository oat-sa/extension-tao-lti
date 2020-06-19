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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\user\events;

use oat\tao\model\webhooks\WebhookSerializableEventInterface;

class LtiTestTakerCreatedEvent implements WebhookSerializableEventInterface
{
    /** @var string */
    private $testTakerUri;

    public function __construct(string $testTakerUri)
    {
        $this->testTakerUri = $testTakerUri;
    }

    public function getName()
    {
        return __CLASS__;
    }

    /**
     * @inheritDoc
     */
    public function getWebhookEventName()
    {
        return 'lti-test-taker-created';
    }

    /**
     * @inheritDoc
     */
    public function serializeForWebhook()
    {
        return [
            'testTakerUri' => $this->testTakerUri,
            'unit' => 1
        ];
    }
}
