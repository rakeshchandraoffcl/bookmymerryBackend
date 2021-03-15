<?php


print_r($_POST['package']);

$query = 'INSERT INTO vendor_package_item (vendor,package,package_item,package_value) VALUES ';
$addon_query = 'INSERT INTO vendor_package_addon (vendor,package,add_on) VALUES ';
foreach ($_POST['package'] as $x => $package) {
    print_r($package);
    foreach ($package['item'] as $y => $val) {
        $query .= '(' . $package['vendor'] . ',' . $package['package'] . ',"' . $val['name'] . '","' . $val['value'] . '"),';
    }
    foreach ($package['add_on'] as $y => $val) {
        $addon_query .= '(' . $package['vendor'] . ',' . $package['package'] . ',"' . $val . '"),';
    }
}


echo $query;
echo $addon_query;
