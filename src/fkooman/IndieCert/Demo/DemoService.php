<?php

/**
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace fkooman\IndieCert\Demo;

use fkooman\Rest\Service;
use fkooman\Http\Request;
use fkooman\Rest\Plugin\Authentication\UserInfoInterface;
use fkooman\Tpl\TemplateManagerInterface;

class DemoService extends Service
{
    /** @var \fkooman\Tpl\TemplateManagerInterface */
    private $templateManager;

    public function __construct(TemplateManagerInterface $templateManager)
    {
        parent::__construct();

        $this->templateManager = $templateManager;
        $this->registerRoutes();
    }

    private function registerRoutes()
    {
        $this->get(
            '/',
            function (Request $request) {
                return $this->templateManager->render('index', array());
            },
            array(
                'fkooman\Rest\Plugin\Authentication\AuthenticationPlugin' => array(
                    'enabled' => false,
                ),
            )
        );

        $this->get(
            '/secret',
            function (Request $request, UserInfoInterface $userInfo) {
                return $this->templateManager->render(
                    'secret',
                    array(
                        'userId' => $userInfo->getUserId(),
                    )
                );
            }
        );
    }
}
