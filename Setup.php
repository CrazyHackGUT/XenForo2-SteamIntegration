<?php

namespace NF\Steam;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;

class Setup extends AbstractSetup
{
	use StepRunnerInstallTrait;
	use StepRunnerUpgradeTrait;
	use StepRunnerUninstallTrait;

    public function install(array $stepParams = [])
    {
//        $this->query("
//            INSERT INTO `xf_connected_account_provider`
//				(`provider_id`, `provider_class`, `display_order`, `options`)
//            VALUES
//            ('steam', 'NF\\\\Steam:Provider\\\\Steam', 80, '');
//        ");
    }

}