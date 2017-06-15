## For official magento2 README, please refer to following link.
	* https://github.com/magento/magento2

## This repo is intended for Magento2 Core Fix. 

## How to use this Fix repo
	* run following command, remeber to specify the branch need in [branch]
        > git submodule add -b [branch] https://github.com/4dhk/magento2.git ./submodules/magento2ce
		e.g. git submodule add -b bugfix_2_1_5 https://github.com/4dhk/magento2.git ./submodules/magento2ce

    * run following command from project directory and also add to deployment script. 
        > sh submodules/magento2ce/Update.sh
