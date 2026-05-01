<?php
/**
 * Open Source Social Network
 *
 * @package   Open Source Social Network (OSSN)
 * @author    OSSN Core Team <info@openteknik.com>
 * @copyright (C) OpenTeknik LLC
 * @license   Open Source Social Network License (OSSN LICENSE)  http://www.opensource-socialnetwork.org/licence
 * @link      https://www.opensource-socialnetwork.org/
 */



$OssnWall = new OssnWall;

// User context
$OssnWall->owner_guid  = ossn_loggedin_user()->guid;
$OssnWall->poster_guid = ossn_loggedin_user()->guid;

if ($OssnWall->owner_guid == 0) {
    ossn_trigger_message(ossn_print('post:create:error'), 'error');
    redirect(REF);
}

// Inputs
$post     = input('post');
$friends  = input('friends');
$location = input('location');
$privacy  = input('privacy');

$privacy = ossn_access_id_str($privacy);
$access  = !empty($privacy) ? input('privacy') : OSSN_FRIENDS;

// -----------------------------
// POST FIRST (CRITICAL)
// -----------------------------
if ($OssnWall->Post($post, $friends, $location, $access)) {

    // -----------------------------
    // IMAGE DETECTION (AFTER POST)
// -----------------------------
    if (isset($OssnWall->OssnFile) && isset($OssnWall->OssnFile->dir)) {

        $image_path = $OssnWall->OssnFile->dir . $OssnWall->OssnFile->newfilename;

        // Python config
        $python = "C:\\Users\\User\\anaconda3\\python.exe";
        $script = "D:\\Research\\project\\scripts\\predict_image_fusion.py";

        $cmd = "\"$python\" \"$script\" \"$image_path\"";
        $result = trim(shell_exec($cmd));

        // Log for debug
        error_log("🧠 IMAGE MODEL OUTPUT: {$result} | path={$image_path}");

        // Warning only (NON-BLOCKING)
        if ($result === "WARNING") {
            ossn_trigger_message(
                "⚠️ This image may contain harmful or abusive content.",
                "warning"
            );
        }
    }

    // Normal OSSN response
    if (ossn_is_xhr()) {
        $guid = $OssnWall->getObjectId();
        $get  = $OssnWall->GetPost($guid);
        if ($get) {
            $get = ossn_wallpost_to_item($get);
            ossn_set_ajax_data([
                'post' => ossn_wall_view_template($get)
            ]);
        }
        exit;
    }

    redirect(REF);

} else {
    ossn_trigger_message(ossn_print('post:create:error'), 'error');
    redirect(REF);
}


