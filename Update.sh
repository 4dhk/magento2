#sh



### This script is assume that the repo is under [project folder]/submodules/smartwaveporto.
### Folder name is not a problem
case "$(uname)" in
	Darwin)
		DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
	;;
	Linux)
		DIR="$( cd "$( dirname $(readlink -f $0) )" && pwd )"
	;;
	*)
		DIR="$( cd "$( dirname $(readlink -f $0) )" && pwd )"
	;;
esac
TargetVendorMagentoDir=$DIR/../../vendor/magento

echo "Workding Directory:"$DIR
echo "Vender Magento Directory:"$TargetVendorMagentoDir

chown -R ubuntu ./*

cp -Rp $DIR/app/code/Magento/Catalog/* $TargetVendorMagentoDir/module-catalog
cp -Rp $DIR/app/code/Magento/Customer/* $TargetVendorMagentoDir/module-customer
cp -Rp $DIR/app/code/Magento/Newsletter/* $TargetVendorMagentoDir/module-newsletter
cp -Rp $DIR/app/code/Magento/SalesRule/* $TargetVendorMagentoDir/module-sales-rule
cp -Rp $DIR/app/code/Magento/Wishlist/* $TargetVendorMagentoDir/module-wishlist
cp -Rp $DIR/app/code/Magento/Swagger/* $TargetVendorMagentoDir/module-swagger
cp -Rp $DIR/app/code/Magento/Theme/* $TargetVendorMagentoDir/module-theme
cp -Rp $DIR/app/code/Magento/Config/* $TargetVendorMagentoDir/module-config
cp -Rp $DIR/app/code/Magento/Email/* $TargetVendorMagentoDir/module-email
cp -Rp $DIR/app/code/Magento/Translation/* $TargetVendorMagentoDir/module-translation

chgrp -R www-data ./*


