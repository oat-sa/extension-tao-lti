<?php

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