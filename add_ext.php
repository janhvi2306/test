<?php
include '../connect.php';
include '../asterisk_execution.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if(isset($_POST['validateExt'])) {
        $extension = htmlspecialchars($_POST['extension']);
        $id = htmlspecialchars($_POST['ext_uuid']);
        $query = "SELECT extension FROM v_extensions where extension = $extension";

        if(!empty($id)) {
            $query .= " and extension_uuid != '$id' ";
        }

        $res = mysqli_query($conn, $query);
        if(mysqli_num_rows($res) > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }

        mysqli_close($conn);
    }


    if(isset($_POST['createExt'])) {
        $extension = htmlspecialchars($_POST['extension']);
        $password = htmlspecialchars($_POST['password']);
        $caller_id = htmlspecialchars($_POST['caller_id']);
        // $context = htmlspecialchars($_POST['context']);
        $user_uuid = htmlspecialchars($_POST['user_uuid']);
        $created_by = htmlspecialchars($_POST['created_by']);
        $call_recording = htmlspecialchars($_POST['call_recording']);

        // $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $created_by = 'Janhvi';

        $sipFile = '/etc/asterisk/sip_custom.conf';
        $extensionsFile = '/etc/asterisk/extension_custom.conf';
        $sipConfig = "\n[$extension]\ntype=friend\nsecret=$password\nhost=dynamic\nqualify=yes\ncallrecording=$call_recording\ncallerid=$caller_id\ncontext=testcalling\n";

        // $extConfig = "\n[$context]\nexten => $extension,1,Answer()\nexten => $extension,n,Wait(1)\nexten => $extension,n,Playback(hello-world)\nexten => $extension,n,Hangup()\n\n";

        file_put_contents($sipFile, $sipConfig, FILE_APPEND | LOCK_EX);

        // file_put_contents($extensionsFile, $extConfig, FILE_APPEND | LOCK_EX);
        // echo exec("sudo asterisk -rx 'sip reload'"); exit;
        if($call_recording == 'yes') {
            execAsteriskCommand('database put callrecording ' . $extension . ' ' . $call_recording);
        }

        execAsteriskCommand('sip reload');

        $query = "INSERT INTO v_extensions(extension_uuid, extension, password, caller_id, user_uuid, call_recording ,created_by) values (UUID(), '$extension', '$password','$caller_id',  '$user_uuid', '$call_recording', '$created_by' )";

        // echo $query;
        // die();

        $res = mysqli_query($conn, $query);

        if(mysqli_affected_rows($conn) > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => true]);
        }

        mysqli_close($conn);

        // echo "Extension $extension created successfully!";
    } 

    if(isset($_POST['type']) && $_POST['type'] == 'updateExt') {

        $extension_uuid = htmlspecialchars($_POST['extension_uuid']);
        $extension = htmlspecialchars($_POST['extension']);
        $currentExtension = htmlspecialchars($_POST['currentExt']);
        $password = htmlspecialchars($_POST['password']);
        $caller_id = htmlspecialchars($_POST['caller_id']);
        $call_recording = htmlspecialchars($_POST['call_recording']);

        // $context = htmlspecialchars($_POST['context']);
        $user_uuid = htmlspecialchars($_POST['user_uuid']);
        $created_by = htmlspecialchars($_POST['created_by']);
        // $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $created_by = 'Janhvi';

        $configFile = '/etc/asterisk/sip_custom.conf'; // Path to your sip_custom.conf
        $updatedContent = [];
        $extensionFound = false;
    
        // Read the existing configuration file
        $lines = file($configFile, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            die("Error reading config file");
        }
    
        foreach ($lines as $line) {
            if (strpos($line, "[$currentExtension]") === 0) {
                $extensionFound = true;
                if($extension != $currentExtension) {
                    $updatedContent[] = "[$extension]";     
                } else {
                    $updatedContent[] = "[$currentExtension]";     
                }

                // print_r($updatedContent);
                // die();

            } elseif ($extensionFound && strpos($line, '[') === 0) {
                $extensionFound = false;
                $updatedContent[] = $line;
    
            } elseif ($extensionFound) {
                if (strpos($line, 'secret') === 0) {
                    $updatedContent[] = "secret=$password";
                } elseif (strpos($line, 'callerid') === 0) {
                    $updatedContent[] = "callerid=$caller_id";
                } elseif (strpos($line, 'callrecording') === 0) {
                    $updatedContent[] = "callrecording=$call_recording";
                } else {
                    $updatedContent[] = $line;
                }
            } else {
                $updatedContent[] = $line;
            }
        }
    
        // Write back the updated content to the config file
        if (file_put_contents($configFile, implode("\n", $updatedContent)) === false) {
            die("Error writing to config file");
        }
    
        if($call_recording == 'yes') {
            execAsteriskCommand('database put callrecording ' . $extension . ' ' . $call_recording);
        }
        
        execAsteriskCommand('sip reload'); 
        // echo "Extension details updated successfully.";
    

        $query = "UPDATE v_extensions SET extension = '$extension', password = '$password' , caller_id = '$caller_id' , user_uuid = '$user_uuid' , call_recording = '$call_recording', created_by = '$created_by' where extension_uuid = '$extension_uuid'";

        // echo $query;
        // die();

        $res = mysqli_query($conn, $query);

        if(mysqli_affected_rows($conn) > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }

        mysqli_close($conn);

    }

    if(isset($_POST['type']) && $_POST['type'] == 'deleteExt') {
        $extension = htmlspecialchars($_POST['extension']);

        $configFile = '/etc/asterisk/sip_custom.conf'; // Path to your sip_custom.conf
        $updatedContent = [];
        $extensionFound = false;
    
        // Read the existing configuration file
        $lines = file($configFile, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            die("Error reading config file");
        }
    
        foreach ($lines as $line) {
            if (strpos($line, "[$extension]") === 0) {
                $extensionFound = true;
                continue;  
            }
            
            if ($extensionFound) {
                if (preg_match('/^\[.*\]$/', $line) || trim($line) === '') {
                    $extensionFound = false;  
                } else {
                    continue; 
                }
            }
        
            $updatedContent[] = $line;
        }

        if (file_put_contents($configFile, implode("\n", $updatedContent)) === false) {
            die("Error writing to config file");
        }

        // echo $query;
        // die();

            execAsteriskCommand('database del callrecording ' . $extension);
            execAsteriskCommand('sip reload'); 

        $query = "DELETE from v_extensions where extension = '$extension' ";
        $res = mysqli_query($conn, $query);

        if(mysqli_affected_rows($conn) > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }

        mysqli_close($conn);

    }
    

    }else {
        echo "Invalid request.";
    }
    
?>
