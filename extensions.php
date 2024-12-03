<?php
include "../connect.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users & Extensions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">


</head>

<body>
    <div class="container mt-5">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Users & Extensions</h4>
                <a href="create_ext.php" style="text-decoration:none;">
                    <button class="btn btn-light btn-sm text-primary fw-semibold">
                        + Create Extension
                    </button>
                </a>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr class="bg-dark text-white">
                            <th>Username</th>
                            <th>Extension</th>
                            <th>Caller ID</th>
                            <th>Call Recording</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                    $res = mysqli_query($conn, "SELECT * FROM v_extensions");
                    while($row = mysqli_fetch_assoc($res)) {
                        echo "<tr>";
                        echo "<td class='fw-semibold text-primary'>Janhvi</td>";  
                        echo "<td class='text-secondary'>{$row['extension']}</td>";
                        echo "<td class='text-secondary'>{$row['caller_id']}</td>";
                        echo "<td class='text-secondary'>{$row['call_recording']}</td>";
                        echo "<td> <a href='edit_ext.php?ext_uuid=".$row['extension_uuid']."'><button class='btn btn-sm btn-outline-primary me-2'><i class='fas fa-edit'></i></button></a>";
                        echo "<button class='btn btn-sm btn-outline-primary me-2' data-bs-toggle='modal' data-bs-target='#deleteExtensionModal' onclick ='setExtension({$row['extension']});'><i class='fas fa-trash'></i></button></a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Extension Modal -->
    <div class="modal fade" id="editExtensionModal" tabindex="-1" aria-labelledby="editExtensionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editExtensionModalLabel">Edit Extension</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <form id="editForm">
                        <!-- Extension Field -->
                        <div class="mb-3">
                            <label for="editExtension" class="form-label">Extension</label>
                            <input type="number" class="form-control" id="editExtension" name="extension"
                                placeholder="Enter extension" readonly>
                        </div>

                        <!-- Username Field -->
                        <div class="mb-3">
                            <label for="editUsername" class="form-label">Username</label>
                            <input type="text" class="form-control" id="editUsername" name="username"
                                placeholder="Enter username" required>
                        </div>

                        <!-- Password Field -->
                        <div class="mb-3">
                            <label for="editPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="editPassword" name="password"
                                placeholder="Enter password" required>
                        </div>

                        <!-- Caller ID Field -->
                        <div class="mb-3">
                            <label for="editCallerId" class="form-label">Caller ID</label>
                            <input type="text" class="form-control" id="editCallerId" name="callerId"
                                placeholder="Enter caller ID" required>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center">
                            <button type="button" onclick="updateExt()" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Extension Modal -->
    <div class="modal fade" id="deleteExtensionModal" tabindex="-1" aria-labelledby="deleteExtensionModal"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <p>
                    <h4>Are you sure to delete ?</h4>
                    </p>
                    <input type="hidden" name="dltExt" id="dltExt" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id = "dltBtn" onclick="dltExtension();">Delete</button>
                </div>
            </div>
        </div>
    </div>

    </tbody>

    </table>

    </div>

</body>


<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function setExtension(ext) {
    var dltExt = document.getElementById('dltExt');
    dltExt.value = ext;

    console.log(dltExt.value);

}

function dltExtension() {
    var dltExtVal = document.getElementById('dltExt').value;
    var dltBtn = document.getElementById('dltBtn');

    dltBtn.innerHTML = "<img src = '../images/search_loader.gif' style = 'width:30px;height:30px;' />";

    $.ajax({
        url: 'add_ext.php',
        method: 'POST',
        data: {
            'extension': dltExtVal,
            'type': 'deleteExt'
        },
        success: function(response) {
            var jsonResponse = JSON.parse(response);
            if (jsonResponse.success) {
                Swal.fire({
                    title: "Status",
                    text: "Extension deleted successfully!",
                    icon: "success"
                });

                setTimeout(function() {
                    window.location.href = 'extensions.php';
                }, 2000);

                dltBtn.innerHTML = 'Delete';
                // document.getElementById("myForm").reset();
            } else {
                Swal.fire({
                    title: "Oops..",
                    text: "Something went wrong!",
                    icon: "error"
                });

                dltBtn.innerHTML = 'Delete';
            }
        },
        error: function(xhr, status, error) {
            // Handle the error response here
            console.error('AJAX Error:', status, error);
        }
    })
}
</script>

</html>