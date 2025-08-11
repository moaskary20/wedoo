<!-- Main Content -->
<div class="main-content">
    <section class="section" id="pill-general_settings" role="tabpanel">
        <div class="section-header mt-2">
            <h1 class="text-capitalize"><?= str_replace('_', ' ', $folder_name)   . "(" . $total_files . ")"; ?></h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="<?= base_url('/partner/dashboard') ?>"><i class="fas fa-home-alt text-primary"></i> <?= labels('Dashboard', 'Dashboard') ?></a></div>
                <div class="breadcrumb-item active"><a href="<?= base_url('/partner/gallery-view') ?>"><i class="fas fa-photo-video text-primary"></i> <?= labels('gallery', "Gallery") ?></a></div>
                <div class="breadcrumb-item"> <?= $folder_name; ?></div>
            </div>
        </div>
        <?php
        if (count($files) > 0) { ?>
            <div class="row mt-4 mb-3">
                <div class="col col d-flex justify-content-end">
                    <button type="button" id="downloadAllBtn" class="btn btn-lg bg-new-primary"><i class="fas fa-arrow-alt-circle-down mr-2"></i><?= labels('download', 'Download') ?></button>
                </div>
            </div>
        <?php } ?>
        <div class="container-fluid card p-3 d-flex">
            <div class="row justify-content-center">
                <?php
            $disk =fetch_current_file_manager();

                if (count($files) == 0) { ?>
                    <div class="col-md-12 d-flex justify-content-center">
                        <div class="empty-state" data-height="400" style="height: 400px;">
                            <div class="empty-state-icon bg-primary">
                                <i class="fas fa-question text-white "></i>
                            </div>
                            <h2>We couldn't find any Files</h2>
                        </div>
                    </div>
                <?php  } else { ?>
                    <?php foreach ($files as $file) : ?>
                        <div class="col-xxl-3 col-xl-2 col-lg-6 col-md-12 mb-3 text-center file-item gallery-file-card ">


                            
                        <?php
                            if ($disk == "local_server") {
                                $file_url = base_url($file['path']);
                                $file_og_path = base_url($file['path']);

                                
                            } else if ($disk == "aws_s3") {
                                $file_url = ($file['full_path']);
                                $file_og_path = ($file['path']);

                            } else {
                                $file_url = base_url($file['path']);
                                $file_og_path = ($file['path']);

                            }


                            ?>
                            <div class="file-preview1" onclick="openFileModal('<?= esc($file['name']) ?>', '<?= esc($file['type']) ?>', '<?= esc($file['size']) ?>', '<?=  $file_og_path ?>','<?=$disk?>','<?=$file['path']?>','<?=$file['full_path']?>')">

                                <?php
                                $fileTypeGeneral = explode('/', $file['type'])[0];
                                if ($fileTypeGeneral === 'image') {
                                    echo "<img class='gallery-file-image' src='" . $file_url . "' alt='" . esc($file['name']) . "' >";
                                } elseif ($fileTypeGeneral === 'video') {
                                    echo "<video width='100' height='100' controls>
                                        <source src='" . $file_url . "' type='" . $file['type'] . "'>
                                        Your browser does not support the video tag.
                                      </video>";
                                } elseif ($fileTypeGeneral === 'audio') {
                                    echo "<audio controls style='width:100px;'>
                                        <source src='" . $file_url . "' type='" . $file['type'] . "'>
                                        Your browser does not support the audio element.
                                      </audio>";
                                } else {
                                    echo "<i class='fa-solid fa-file text-primary' style='font-size: 50px;'></i>";
                                }
                                ?>
                            </div>
                            <div class="mt-2">
                                <?php
                                $fileName = esc($file['name']);
                                if (strlen($fileName) > 15) {
                                    $truncated = substr($fileName, 0, 15);
                                    $lastSpace = max(strrpos($truncated, ' '), strrpos($truncated, '-'));
                                    if ($lastSpace !== false) {
                                        $truncated = substr($truncated, 0, $lastSpace);
                                    }
                                    echo $truncated . '...';
                                } else {
                                    echo $fileName;
                                }
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php } ?>
            </div>
        </div>
    </section>
</div>
<!-- Modal -->
<div class="modal fade" id="fileModal" tabindex="-1" role="dialog" aria-labelledby="fileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header m-0 p-0" style="border-bottom: solid 1px #e5e6e9;">
                <div class="row pl-3">
                    <div class="col ">
                        <div class="toggleButttonPostition">
                            <h5 class="modal-title" id="fileModalLabel"></h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <div id="filePreview" class="text-center mb-3"></div>
                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary mr-2" onclick="downloadFile()">DOWNLOAD</button>
                    <button class="btn btn-info" onclick="copyFilePath()">COPY PATH</button>
                    <button class="btn btn-secondary ml-2" onclick="copyFullFilePath()">COPY FULL PATH</button>
                    <button class="btn btn-danger ml-2" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    let currentFilePath = '';
    let original_path ='';


    function openFileModal(name, type, size, path,disk,og_path,full_path) {
        document.getElementById('fileModalLabel').textContent = name;

        if(disk=="aws_s3"){

            currentFilePath = full_path;

        }else if(disk=="local_server"){
            currentFilePath = path;
        }else{
            currentFilePath = path;
            
        }
        
        original_path=path;

        
        
        const preview = document.getElementById('filePreview');
        preview.innerHTML = '';
        const fileTypeGeneral = type.split('/')[0];
        if (fileTypeGeneral === 'image') {
            preview.innerHTML = `<img src="${currentFilePath}" alt="${name}" style="max-width: 100%; max-height: 400px;">`;
        } else if (fileTypeGeneral === 'video') {
            preview.innerHTML = `<video width="100%" height="auto" controls>
                                <source src="${currentFilePath}" type="${type}">
                                Your browser does not support the video tag.
                             </video>`;
        } else if (fileTypeGeneral === 'audio') {
            preview.innerHTML = `<audio controls style="width:100%;">
                                <source src="${currentFilePath}" type="${type}">
                                Your browser does not support the audio element.
                             </audio>`;
        } else {
            preview.innerHTML = `<i class="fa-solid fa-file text-primary" style="font-size: 100px;"></i>`;
        }
        $('#fileModal').modal('show');
    }
    function downloadFile() {
        window.open(currentFilePath, '_blank');
    }

    function copyFilePath() {
       

        const baseUrl = '<?= base_url() ?>';
        const pathWithoutBase = original_path.replace(baseUrl, '');
        navigator.clipboard.writeText(pathWithoutBase).then(() => {
            showToastMessage("File path copied to clipboard", "success");
        }).catch(err => {
            showToastMessage("Failed to copy", "error");
            console.error('Failed to copy: ', err);
        });
    }

    function copyFullFilePath() {
        navigator.clipboard.writeText(currentFilePath).then(() => {
            showToastMessage("Full file path copied to clipboard", "success");
        }).catch(err => {
            showToastMessage("Failed to copy", "error");
            console.error('Failed to copy: ', err);
        });
    }

    function downloadAllFiles() {
        showToastMessage("Preparing files for download...", "info");
        $.ajax({
            url: '<?= base_url("partner/gallery/download-all") ?>',
            method: 'POST',
            data: {
                folder: '<?= $folder_name ?>',
                full_path: '<?= $path ?>',
                disk:'<?=$disk?>',
            },
            xhrFields: {
                responseType: 'blob'
            },
            success: function(data) {
                var blob = new Blob([data], {
                    type: 'application/zip'
                });
                var url = window.URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = '<?= $folder_name ?>.zip';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                showToastMessage("Download started", "success");
            },
            error: function() {
                showToastMessage("Failed to generate download", "error");
            }
        });
    }
  
    document.getElementById('downloadAllBtn').addEventListener('click', downloadAllFiles);
</script>