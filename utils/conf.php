<?php

const backgroundColor = "bgColor";
const peerPage = "peerPage";
const width = "width";
const minWidth = "minWidth";
const lineStyle = "line";
const lineColor = "lnColor";

function getRepeatConfig():array {
    $conf = kv_get("sl_follow_repeat_json_conf");
    if (!empty($conf)) return (json_decode($conf,true));
    return [
        backgroundColor=> setting_get('sl_repeat_follow_color') ?? "#082f3e",
        peerPage=> setting_get('sl_repeat_follow_perpage') ?? 10,
        width=> setting_get("sl_repeat_follow_b_w") ?? 80,
        minWidth=> setting_get("sl_repeat_follow_b_mw") ?? 200,
        lineStyle=> setting_get("sl_repeat_follow_b_t") ?? 0,
        lineColor=> setting_get("sl_repeat_follow_b_c") ?? "#082f3e"
    ];
}
function getRepeatConfValueOrDefault(array $conf, string $key) {
    return $conf[$key] ?? getRepeatConfig()[$key];
}
function setRepeatConfig(array $conf):bool {
    return kv_set("sl_follow_repeat_json_conf",json_encode([
        backgroundColor=> getRepeatConfValueOrDefault($conf,backgroundColor),
        peerPage=> getRepeatConfValueOrDefault($conf,peerPage),
        width=> getRepeatConfValueOrDefault($conf,width),
        minWidth=> getRepeatConfValueOrDefault($conf,minWidth),
        lineStyle=> getRepeatConfValueOrDefault($conf,lineStyle),
        lineColor=> getRepeatConfValueOrDefault($conf,lineColor),
    ]));
}