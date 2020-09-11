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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA
 */

namespace oat\taoLti\models\tool\service;

use oat\oatbox\service\ConfigurableService;
use oat\taoLti\models\classes\LtiProvider\LtiProvider;

class LtiToolJwkProvider extends ConfigurableService
{
    public function getPublicKey(LtiProvider $ltiProvider): string
    {
        // @TODO Get key from provider
        return '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAyZXlfd5yqChtTH91N76V
okquRu2r1EwNDUjA0GAygrPzCpPbYokasxzs+60Do/lyTIgd7nRzudAzHnujIPr8
GOPIlPlOKT8HuL7xQEN6gmUtz33iDhK97zK7zOFEmvS8kYPwFAjQ03YKv+3T9b/D
brBZWy2Vx4Wuxf6mZBggKQfwHUuJxXDv79NenZarUtC5iFEhJ85ovwjW7yMkcflh
Ugkf1o/GIR5RKoNPttMXhKYZ4hTlLglMm1FgRR63pvYoy9Eq644a9x2mbGelO3Hn
GbkaFo0HxiKbFW1vplHzixYCyjc15pvtBxw/x26p8+lNthuxzaX5HaFMPGs10rRP
LwIDAQAB
-----END PUBLIC KEY-----';
    }
}
