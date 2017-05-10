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

chgrp -R www-data ./*


