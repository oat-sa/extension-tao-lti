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
  * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
  *
  */
namespace oat\taoLti\models\classes;

use common_http_Request;
use common_user_auth_Adapter;

interface FactoryLtiAuthAdapterServiceInterface
{
    const SERVICE_ID = 'taoLti/FactoryLtiAuthAdapter';

    /**
     * @param common_http_Request $request
     * @return common_user_auth_Adapter
     */
    public function create(common_http_Request $request);
}
