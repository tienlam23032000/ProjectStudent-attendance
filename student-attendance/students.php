<?php include('db_connect.php');


?>

<div class="container-fluid">
    <div class="col-lg-12">
        <div class="row mb-4 mt-4">
            <div class="col-md-12">
            </div>
        </div>
        <div class="row">
            <!-- FORM Panel -->
            <!-- Table Panel -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <b>List of Student</b>
                        <span class="float:right">
                            <a class="btn btn-primary btn-block btn-sm col-sm-2 float-right" href="javascript:void(0)" id="new_student">
                                <i class="fa fa-plus"></i> New Student
                            </a></span>

                            <input type="file" id="excelFile" class="form-control" />
                                <button type="submit" id="submitExcelFile" class="btn btn-success">ImportExcel</button>
                                
                            

                    </div>
                    <div class="card-body">
                        <table class="table table-condensed table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="">ID #</th>
                                    <th class="">Name</th>
                                    <th class="">Class</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                $student = $conn->query("SELECT s.*,concat(co.course,' ',c.level,'-',c.section) as `class` FROM students s inner join `class` c on c.id = s.class_id inner join courses co on co.id = c.course_id order by s.name desc ");
                                while ($row = $student->fetch_assoc()) :
                                ?>
                                    <tr>
                                        <td class="text-center"><?php echo $i++ ?></td>
                                        <td>
                                            <p> <b><?php echo $row['id_no'] ?></b></p>
                                        </td>
                                        <td>
                                            <p> <b><?php echo ucwords($row['name']) ?></b></p>
                                        </td>
                                        <td class="">
                                            <p> <b><?php echo $row['class'] ?></b></p>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-primary edit_student" type="button" data-id="<?php echo $row['id'] ?>">Edit</button>
                                            <button class="btn btn-sm btn-outline-danger delete_student" type="button" data-id="<?php echo $row['id'] ?>">Delete</button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Table Panel -->
        </div>
    </div>

</div>
<style>
    td {
        vertical-align: middle !important;
    }

    td p {
        margin: unset
    }

    img {
        max-width: 100px;
        max-height: 150px;
    }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.7.7/xlsx.core.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xls/0.7.4-a/xls.core.min.js"></script>
<script>
    $(document).ready(function() {
        $('table').dataTable()
    })
    $('#new_student').click(function() {
        uni_modal("New student", "manage_student.php", "")
    })

    $('.edit_student').click(function() {
        uni_modal("Manage student Details", "manage_student.php?id=" + $(this).attr('data-id'), "mid-large")

    })
    $('.delete_student').click(function() {
        _conf("Are you sure to delete this student?", "delete_student", [$(this).attr('data-id')])
    })

    $('#submitExcelFile').click(function() {
        //Get file blob
        var file = $('#excelFile')[0].files[0];
        var lstTypeFile = [{
            uid: 1,
            type: 'application/vnd.ms-excel'
        }, {
            uid: 2,
            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        }];

        //Check type file equals lstTypeFile
        var fileType = lstTypeFile.find(x => x.type == file.type)
        if (!file) {
            alert_toast('Chose file please !', 'danger')
            return;
        }
        if (!fileType) {
            alert_toast('File is not format !', 'danger')
            return;
        }
        if (typeof(FileReader) == "undefined") {
            alert_toast('Browser does not support!', 'danger')
            return;
        }
        excel_to_json(file, fileType.uid);
    })

    function excel_to_json(file, type) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var data = e.target.result;
            var workbook = type == 2 ?
                XLSX.read(data, {
                    type: 'binary'
                }) :
                XLS.read(data, {
                    type: 'binary'
                });
            //Get first sheet in wookbook
            var firstSheet = workbook.SheetNames[0];
            //convert sheet to Json
            var excelJson = type == 2 ?
                XLSX.utils.sheet_to_json(workbook.Sheets[firstSheet], {
                    raw: true
                }) :
                XLS.utils.sheet_to_row_object_array(workbook.Sheets[firstSheet], {
                    raw: true
                });
            if (excelJson?.length < 0) {
                alert_toast('File is empty !', 'danger')
                return;
            }
            import_excel(excelJson)
        }
        //check error
        reader.onerror = function(ex) {
            console.log(ex);
        };
        reader.readAsBinaryString(file);
    }


    function import_excel(json) {
        start_load();
        $.ajax({
            url: "ajax.php?action=import_excel",
            method: "POST",
            data: {
                json: JSON.stringify(json)
            },
            success: function(resp) {
                const respPaser = JSON.parse(resp)
                end_load();
                alert_toast(`<p>${respPaser.countSuccess} record update, ${respPaser.countVaild} already exist</p>`, 'success', 5000)
            },
            error: function(err) {
                end_load();
                console.error(err);
            }
        });
    }

    function delete_student($id) {
        start_load()
        $.ajax({
            url: 'ajax.php?action=delete_student',
            method: 'POST',
            data: {
                id: $id
            },
            success: function(resp) {
                if (resp == 1) {
                    alert_toast("Data successfully deleted", 'success')
                    setTimeout(function() {
                        location.reload()
                    }, 1500)

                }
            }
        })
    }
</script>