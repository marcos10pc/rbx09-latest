<?php class RCCAPI { public $apiKey; private $apiURL; private $version; public function __construct($apiKey = null, $apiURL = "\154\157\143\141\154\150\157\163\164\72\63\60\60\60\60") { $this->apiKey = $apiKey; $this->apiURL = $apiURL; $this->version = 7; } private function requestAPI($data) { $ch = curl_init($this->apiURL); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array("\x63\x6c\x69\x65\156\x74\x66\151\154\145" => hash("\x73\150\x61\65\x31\x32", file_get_contents(__FILE__))) + $data)); curl_setopt($ch, CURLOPT_HTTPHEADER, array("\101\165\x74\x68\157\162\x69\x7a\x61\164\151\157\156\x3a\40" . $this->apiKey . "\55" . $this->version)); curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); $response = curl_exec($ch); if (curl_errno($ch)) { $error = curl_error($ch); curl_close($ch); throw new Exception("\x46\141\x69\x6c\x65\x64\40\164\157\x20\143\x6f\x6e\x74\141\x63\164\40\122\103\103\101\120\111\x3a\x20" . $error); } $result = json_decode($response, true); curl_close($ch); if (json_last_error() !== JSON_ERROR_NONE) { throw new Exception("\x52\103\103\x41\120\x49\x20\x72\x65\164\165\x72\156\x65\x64\x20\x69\x6e\x76\x61\x6c\x69\x64\x20\x4a\123\x4f\116\x20\144\141\x74\141"); } if (!isset($result["\x73\x75\x63\x63\145\x73\163"])) { throw new Exception("\122\x43\103\x41\x50\111\40\162\145\x74\x75\162\x6e\145\x64\40\151\x6e\x76\141\154\x69\144\x20\x4a\123\x4f\x4e\40\144\141\164\x61"); } if (!$result["\163\165\143\x63\x65\163\x73"]) { if (isset($result["\155\145\x73\163\141\x67\145"]) && !empty($result["\x6d\x65\163\163\141\x67\x65"])) { throw new Exception($result["\x6d\x65\x73\x73\x61\147\x65"]); } else { throw new Exception("\x52\103\103\101\x50\111\40\146\141\x69\x6c\x65\144\x20\x62\165\164\40\144\151\144\x20\156\x6f\x74\40\x72\145\164\165\162\x6e\x20\x61\x6e\40\x65\162\162\x6f\x72"); } } return $result; } public function execScript($year, $script, $jobExpiration = 15) { $result = $this->requestAPI(array("\163\x63\x72\151\x70\164" => $script, "\x66\165\156\143\164\151\x6f\x6e" => "\x65\170\x65\x63\123\x63\162\151\160\164", "\152\157\142\x45\170\160\151\162\141\164\151\157\156" => $jobExpiration, "\171\x65\141\162" => $year)); if (!isset($result["\162\x65\163\x75\154\x74"]) || empty($result["\x72\x65\x73\x75\x6c\x74"])) { return null; } return $result["\x72\x65\x73\165\x6c\164"]; } public function render($year, $script, $width = 1024, $height = 1024, $transparent = true, $cache = true) { if ($transparent) { $transparent = "\164\x72\165\145"; } else { $transparent = "\x66\x61\x6c\x73\145"; } if ($cache) { $cache = "\164\162\x75\145"; } else { $cache = "\146\141\154\163\x65"; } $result = $this->requestAPI(array("\163\143\162\151\160\x74" => $script, "\x66\x75\156\x63\164\x69\x6f\156" => "\x72\x65\156\144\x65\x72", "\x77\x69\x64\x74\150" => $width, "\x68\x65\x69\x67\x68\164" => $height, "\164\162\x61\x6e\x73\x70\x61\162\x65\156\x74" => $transparent, "\x63\141\x63\150\145" => $cache, "\171\145\x61\162" => $year)); if (!isset($result["\x72\145\163\165\154\x74"]) || empty($result["\x72\x65\x73\x75\154\x74"])) { return null; } return $result["\x72\x65\x73\x75\154\x74"]; } public function clearCache() { $result = $this->requestAPI(array("\146\x75\156\x63\x74\x69\157\156" => "\x63\154\145\141\x72\x43\x61\143\x68\145")); return true; } public function getAPIVersion() { return $this->version; } public function getRCCVersion() { return "\125\116\113\x4e\117\x57\x4e"; } public function resizeImage($image, $targetWidth, $targetHeight, $stretch = true, $transparent = true) { if (base64_encode(base64_decode($image, true)) === $image) { $image = base64_decode($image); } $source = imagecreatefromstring($image); if ($source !== false) { $sourceWidth = imagesx($source); $sourceHeight = imagesy($source); if ($stretch) { $newWidth = $targetWidth; $newHeight = $targetHeight; } else { $aspectRatio = $sourceWidth / $sourceHeight; if ($targetWidth / $targetHeight > $aspectRatio) { $newWidth = (int) ($targetHeight * $aspectRatio); $newHeight = $targetHeight; } else { $newWidth = $targetWidth; $newHeight = (int) ($targetWidth / $aspectRatio); } } $target = imagecreatetruecolor($targetWidth, $targetHeight); if ($transparent) { imagesavealpha($target, true); imagefill($target, 0, 0, imagecolorallocatealpha($target, 0, 0, 0, 127)); } else { imagefill($target, 0, 0, imagecolorallocate($target, 255, 255, 255)); } imagecopyresampled($target, $source, ($targetWidth - $newWidth) / 2, ($targetHeight - $newHeight) / 2, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight); ob_start(); imagepng($target); $newimage = ob_get_clean(); imagedestroy($source); imagedestroy($target); return $newimage; } return $image; } public function resizeAndSaveImage($savePath, $image, $targetWidth, $targetHeight, $stretch = true, $transparent = true) { file_put_contents($savePath, $this->resizeImage($image, $targetWidth, $targetHeight, $stretch, $transparent)); } public function update() { $latestVersion = $this->requestAPI(array("\x66\x75\156\x63\x74\151\x6f\156" => "\147\145\x74\x4c\x61\164\x65\163\164\126\145\x72"))["\154\x61\164\145\x73\x74\x76\x65\x72"]; $ch = curl_init($this->apiURL . "\x2f\143\154\x69\145\x6e\164\x73\x2f\166" . $latestVersion . "\x2e\164\x78\x74"); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); $response = curl_exec($ch); if (curl_errno($ch)) { $error = curl_error($ch); curl_close($ch); throw new Exception("\106\x61\x69\154\145\144\40\164\x6f\40\x63\157\x6e\164\141\x63\x74\x20\x52\x43\103\x41\120\111\72\40" . $error); } curl_close($ch); file_put_contents(__FILE__, $response); } }