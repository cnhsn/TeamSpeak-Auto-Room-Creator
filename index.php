<?php
echo "<title>Room Creator by Sn0bzy</title>";
$nickname = $client = '';
require_once("libraries/TeamSpeak3/TeamSpeak3.php");

function string_ends_with($string, $ending)
{
    $len = strlen($ending);
    $string_end = substr($string, strlen($string) - $len);
    return $string_end == $ending;
}
//CONFIG
//channel admin group id
$groupid = 5;
//number of channels in spacer
$chinspacer = 25;
//connection details
$ts3_VirtualServer = TeamSpeak3::factory("serverquery://name:password@127.0.0.1:10011/?server_port=9987");

$ts3_VirtualServer->selfUpdate(array('client_nickname'=>"Permanent room"));
//DO NOT EDIT THIS UNLESS YOU KNOW WHAT YOU ARE DOING
$greekalphabet = array("?","ß","?","?","?","?","?","?","?","?","?","µ","?","?","?","?","?","?","?","?","?","?","?","?");
$isonts = false;
foreach($ts3_VirtualServer->clientList() as $client)
{
    if($client->client_type) continue;
    if($client->getProperty('connection_client_ip') == $_SERVER['REMOTE_ADDR'])
    {
        $client = $client;
        $nickname = $client->getProperty('client_nickname');
        $uid = $client->getProperty('client_unique_identifier');
        $dbid = $client->getProperty('client_database_id');
        $isonts = true;
    }
}
if($isonts == true)
{
try{
$grouplist = $ts3_VirtualServer->channelGroupClientList($groupid,null,$dbid);
foreach($grouplist as $group)
{
    $g = $group['cid'];
    break;
}
$ts3_VirtualServer->channelListReset();
if($ts3_VirtualServer->channelGetById($g)->getProperty('channel_flag_permanent') == 0 && $ts3_VirtualServer->channelGetById($g)->getProperty('channel_flag_semi_permanent') == 0)
{
    $greek = 0;
    foreach($ts3_VirtualServer->channelList() as $channel)
    {
        if(!$channel->isSpacer()) {continue;}
        if(string_ends_with($channel,"v")) { $greek = $greek + 1; $spacer = $channel;}
    }
    $count=0;
    foreach($spacer->subChannelList() as $subchannel)
    {
        $count = $count + 1;
        $cid = $subchannel->getProperty('cid');
    }
    if($count >= $chinspacer)
    {
        $ts3_VirtualServer->channelCreate(array("channel_name=[cspacer".$greekalphabet[$greek]."]v - Sector ".$greekalphabet[$greek]." - v","channel_order=".$spacer->getProperty('cid'),"channel_flag_permanent=1","channel_flag_maxfamilyclients_unlimited=1"));
        $ts3_VirtualServer->channelListReset();
        foreach($ts3_VirtualServer->channelList() as $channel)
        {
        if(!$channel->isSpacer()) {continue;}
        if(string_ends_with($channel,"v")) {$spacer = $channel;}
        }
        $ts3_VirtualServer->channelMove($channel->getProperty('cid'),$spacer->getProperty('cid'));
        $channel->modify(array("channel_flag_permanent=1"));
        $client = $ts3_VirtualServer->clientGetByDbid($group['cldbid']);
        $client->move($g);
        $client->setChannelGroup($channel->getProperty('cid'),$groupid);
    }
    else
    {
        $ts3_VirtualServer->channelMove($channel->getProperty('cid'),$spacer->getProperty('cid'),$cid);
        $channel->modify(array("channel_flag_permanent=1","channel_flag_maxfamilyclients_unlimited=1"));
    }
    header('Location: index.php');
}
else { echo "You are not in temporary room!!";}
}
catch (exception $e) {echo "Something went wrong: " . $e->getMessage();}

}
else {echo "You are not connected!";}

?>