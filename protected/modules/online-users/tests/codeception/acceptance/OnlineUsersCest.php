<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace onlineUsers\acceptance;

use onlineUsers\AcceptanceTester;

class OnlineUsersCest
{
    public function testDashboardSidebar(AcceptanceTester $I)
    {
        $I->wantTo('test dashboard sidebar with online-users');
        $I->amAdmin();
        $I->waitForText('Online users (1)');
    }

}
