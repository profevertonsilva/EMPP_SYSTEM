<style>
    .file-preview-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 280px;
        max-height: 420px;
        overflow: hidden;
        background: #0e1320;
        border-radius: var(--empp-radius);
    }
    #new-photo-preview {
        max-width: 100%;
        max-height: 420px;
        object-fit: contain;
    }
    #imageCropModal .modal-body {
        height: 70vh;
        min-height: 420px;
        background: var(--empp-bg);
        padding: 0;
    }
    #crop-modal-image {
        width: 100%;
        height: 100%;
        display: block;
    }
    .crop-modal-workspace {
        width: 100%;
        height: 100%;
        min-height: 420px;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
    }
    #imageCropModal .cropper-container {
        width: 100% !important;
        height: 100% !important;
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <?php $empp_step = 1; include __DIR__ . '/../includes/dashboard/new_research_steps.php'; ?>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <form id="formAuthentication" action="/dashboard/researcher/research/upload-file" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <input type="file" id="ree_file-photo" name="ree_file" accept="image/jpeg, image/png, image/webp" class="d-none">

                            <!-- Empty state: pick or drop a file -->
                            <label for="ree_file-photo" class="empp-dropzone research-preview" id="ree-dropzone">
                                <i class="bx bx-image-add"></i>
                                <strong>Drop the SEM image here or click to choose</strong>
                                <span>JPG, PNG or WEBP &middot; up to 5 MB</span>
                            </label>

                            <!-- Selected file: cropped preview -->
                            <div id="preview-container" style="display: none;">
                                <div class="file-preview-container mt-4">
                                    <img id="new-photo-preview" alt="Selected SEM image">
                                </div>
                                <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
                                    <button type="button" id="cancel-upload" class="btn btn-outline-secondary">
                                        <i class="bx bx-x me-1"></i> Choose another image
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        Continue <i class="bx bx-right-arrow-alt ms-1"></i>
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="modal fade" id="imageCropModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <div>
                                            <span class="empp-eyebrow">Step 1 of 2</span>
                                            <h5 class="modal-title">Crop the image</h5>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-3 p-md-4">
                                        <div class="crop-modal-workspace">
                                            <img id="crop-modal-image" alt="Image to crop">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" id="reset-crop" class="btn btn-outline-secondary">
                                            <i class="bx bx-refresh me-1"></i> Reset crop
                                        </button>
                                        <button type="button" id="apply-crop" class="btn btn-primary">
                                            <i class="bx bx-crop me-1"></i> Apply crop
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <span class="empp-eyebrow mb-2">Before you upload</span>
                        <ul class="ps-3 mb-0 text-muted">
                            <li class="mb-2">Use a SEM micrograph of the membrane surface.</li>
                            <li class="mb-2">Crop out the microscope's information bar and scale bar; only the fibers should be analysed.</li>
                            <li>Keep the same magnification across samples you want to compare.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Drag & drop onto the dropzone feeds the same file input (and its change handler)
    (function() {
        const zone = document.getElementById('ree-dropzone');
        const input = document.getElementById('ree_file-photo');
        ['dragenter', 'dragover'].forEach(function(evt) {
            zone.addEventListener(evt, function(e) { e.preventDefault(); zone.classList.add('is-dragover'); });
        });
        ['dragleave', 'drop'].forEach(function(evt) {
            zone.addEventListener(evt, function(e) { e.preventDefault(); zone.classList.remove('is-dragover'); });
        });
        zone.addEventListener('drop', function(e) {
            if (!e.dataTransfer.files.length) return;
            const dt = new DataTransfer();
            dt.items.add(e.dataTransfer.files[0]);
            input.files = dt.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
        // Hide the dropzone while an image is selected; the page script shows it again on cancel
        // (deferred so it runs after the page script has validated/cleared the input)
        input.addEventListener('change', function() {
            setTimeout(function() { zone.style.display = input.files.length ? 'none' : ''; }, 0);
        });
    })();
    </script>



    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css"
      integrity="sha384-6LFfkTKLRlzFtgx8xsWyBdKGpcMMQTkv+dB7rAbugeJAu1Ym2q1Aji1cjHBG12Xh" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"
      integrity="sha384-jrOgQzBlDeUNdmQn3rUt/PZD+pdcRBdWd/HWRqRo+n2OR2QtGyjSaJC0GiCeH+ir" crossorigin="anonymous"></script>

    <script>
    $(document).ready(function() {
        const formAuthentication = $('#formAuthentication');
        const profilePhoto = $('#ree_file-photo');
        const previewContainer = $('#preview-container');
        const cancelBtn = $('#cancel-upload');
        const applyCropBtn = $('#apply-crop');
        const resetCropBtn = $('#reset-crop');
        const imageCropModalEl = document.getElementById('imageCropModal');
        const modalCropImage = document.getElementById('crop-modal-image');
        const previewContainerInner = $('.file-preview-container');
        const imageCropModal = (typeof bootstrap !== 'undefined' && imageCropModalEl)
            ? new bootstrap.Modal(imageCropModalEl, { backdrop: 'static' })
            : null;
        let cropper = null;
        let originalImageDataUrl = null;
        let pendingImageToCrop = null;
        let selectedFileName = '';
        let selectedMimeType = '';
        let isSubmittingAfterCrop = false;

        // Quando um arquivo é selecionado
        profilePhoto.change(function(e) {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                selectedFileName = file.name;
                selectedMimeType = file.type;
                
                if (!validateImage(file)) {
                    $(this).val('');
                    return;
                }
                
                createStandardPreview(file);
            }
        });

        // Cancelar upload
        cancelBtn.click(function() {
            resetPreview();
        });

        applyCropBtn.click(function() {
            applyCurrentCrop(false);
        });

        resetCropBtn.click(function() {
            if (originalImageDataUrl) {
                initializeCropper(originalImageDataUrl, true);
            }
        });

        if (imageCropModalEl) {
            imageCropModalEl.addEventListener('hidden.bs.modal', function() {
                destroyCropper();
            });
        }

        formAuthentication.on('submit', function(e) {
            if (cropper && !isSubmittingAfterCrop) {
                e.preventDefault();
                applyCurrentCrop(true);
            }
        });

        // Validação da imagem
        function validateImage(file) {
            // Tamanho máximo: 5MB
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire({
                    title: 'File too large',
                    text: 'The maximum allowed size is 5MB',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false;
            }
            
            // Tipos permitidos
            const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                Swal.fire({
                    title: 'Invalid file type',
                    text: 'Only JPG, PNG and WEBP files are allowed',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false;
            }
            
            return true;
        }

        function createStandardPreview(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                originalImageDataUrl = e.target.result;
                initializeCropper(e.target.result);
            };
            reader.readAsDataURL(file);
        }

        function initializeCropper(imageSrc, keepModalOpen = false) {
            destroyCropper();
            pendingImageToCrop = imageSrc;
            previewContainer.show();

            if (imageCropModal) {
                if (!keepModalOpen) {
                    imageCropModal.show();
                }
            }

            renderCropperFromPendingImage();
        }

        function renderCropperFromPendingImage() {
            if (!pendingImageToCrop || !modalCropImage) {
                return;
            }

            modalCropImage.src = pendingImageToCrop;

            modalCropImage.onload = function() {
                destroyCropper();

                cropper = new Cropper(modalCropImage, {
                    viewMode: 1,
                    aspectRatio: NaN,
                    dragMode: 'move',
                    autoCropArea: 1,
                    responsive: true,
                    background: false,
                    movable: true,
                    zoomable: true,
                    rotatable: false,
                    scalable: false,
                    cropBoxResizable: true,
                    cropBoxMovable: true,
                    minContainerWidth: 900,
                    minContainerHeight: 420,
                    ready: function() {
                        // Ajusta o zoom inicial para exibir a imagem completa no quadro.
                        const containerData = cropper.getContainerData();
                        const imageData = cropper.getImageData();

                        if (!containerData.width || !containerData.height || !imageData.naturalWidth || !imageData.naturalHeight) {
                            return;
                        }

                        const fitRatio = Math.min(
                            containerData.width / imageData.naturalWidth,
                            containerData.height / imageData.naturalHeight
                        );

                        cropper.zoomTo(fitRatio);

                        const fittedImageData = cropper.getImageData();
                        cropper.setCropBoxData({
                            left: fittedImageData.left,
                            top: fittedImageData.top,
                            width: fittedImageData.width,
                            height: fittedImageData.height
                        });
                    }
                });

                pendingImageToCrop = null;
                modalCropImage.onload = null;
            };
        }

        function applyCurrentCrop(submitAfter) {
            if (!cropper) {
                if (submitAfter) {
                    isSubmittingAfterCrop = true;
                    formAuthentication[0].submit();
                }
                return;
            }

            const outputMimeType = getOutputMimeType();
            const canvas = cropper.getCroppedCanvas({
                fillColor: '#ffffff'
            });

            if (!canvas) {
                Swal.fire({
                    title: 'Crop error',
                    text: 'Unable to crop image. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }

            canvas.toBlob(function(blob) {
                if (!blob) {
                    Swal.fire({
                        title: 'Crop error',
                        text: 'Unable to generate cropped image.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                const croppedFileName = buildCroppedFileName(selectedFileName, blob.type);
                const croppedFile = new File([blob], croppedFileName, {
                    type: blob.type,
                    lastModified: Date.now()
                });

                replaceInputFile(croppedFile);
                selectedFileName = croppedFileName;
                selectedMimeType = blob.type;

                const croppedImageUrl = URL.createObjectURL(blob);
                showSuccessPreview(croppedImageUrl);

                if (imageCropModal) {
                    imageCropModal.hide();
                }

                if (submitAfter) {
                    isSubmittingAfterCrop = true;
                    formAuthentication[0].submit();
                } else {
                    Swal.fire({
                        title: 'Crop applied',
                        text: 'The image has been cropped and is ready to upload.',
                        icon: 'success',
                        timer: 1800,
                        showConfirmButton: false
                    });
                }
            }, outputMimeType, 0.95);
        }

        function getOutputMimeType() {
            if (selectedMimeType === 'image/png') return 'image/png';
            if (selectedMimeType === 'image/webp') return 'image/webp';
            return 'image/jpeg';
        }

        function replaceInputFile(file) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            profilePhoto[0].files = dataTransfer.files;
        }

        function buildCroppedFileName(fileName, mimeType) {
            const nameWithoutExtension = fileName.replace(/\.[^/.]+$/, '');
            const extensionMap = {
                'image/jpeg': 'jpg',
                'image/png': 'png',
                'image/webp': 'webp'
            };
            const extension = extensionMap[mimeType] || 'jpg';
            return nameWithoutExtension + '_cropped.' + extension;
        }

        function destroyCropper() {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
        }

        function showSuccessPreview(imageSrc) {
            destroyCropper();
            previewContainerInner.empty();
            previewContainerInner.append('<img id="new-photo-preview" class="img-fluid">');
            $('#new-photo-preview').attr('src', imageSrc);
            previewContainer.show();
        }

        function resetPreview() {
            destroyCropper();
            originalImageDataUrl = null;
            selectedFileName = '';
            selectedMimeType = '';
            isSubmittingAfterCrop = false;
            pendingImageToCrop = null;
            profilePhoto.val('');
            previewContainer.hide();
            if (imageCropModal) {
                imageCropModal.hide();
            }
            $('.research-preview').show();
            previewContainerInner.empty();
        }

        // Tratar mensagens de sucesso/erro da URL
        const urlParams = new URLSearchParams(window.location.search);
        const successParam = urlParams.get('success');
        
        const messages = {
            '0': { title: 'Error!', text: 'Failed to update the image', icon: 'error' },
            '1': { title: 'Success!', text: 'Image updated successfully', icon: 'success' }
        };
        
        if (messages[successParam]) {
            const cleanURL = window.location.pathname;
            history.replaceState(null, '', cleanURL);
            
            Swal.fire({
                title: messages[successParam].title,
                text: messages[successParam].text,
                icon: messages[successParam].icon,
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        }
    });
    </script>