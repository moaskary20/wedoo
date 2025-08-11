<div class="main-content">
    <section class="section">
        <div class="container-fluid card">
            <div class="section-header">
                <h1><?= labels('update_categories', "Update categories") ?></h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="<?= base_url('/admin/dashboard') ?>"><?= labels('Dashboard', 'Dashboard') ?></a></div>
                    <div class="breadcrumb-item"><a href="<?= base_url('/admin/dashboard/Category') ?>">Category</a></div>
                    <div class="breadcrumb-item">Update Category</div>
                </div>
            </div>
            <?= helper('form'); ?>
            <div class="row">
                <div class="col-md">
                    <h2 class='section-title'><?= labels('create_category', "Create Category") ?></h2>
                    <div class="card-body">
                        <div class="card-body">
                            <?= form_open('/admin/categories/update_category_process', ['method' => "post", 'id' => 'update_category_process']); ?>
                            <div class="form-group">
                                <input id="id" class="form-control" type="hidden" name="id" placeholder="Enter the name of the Category here" value="<?= $category_data['id'] ?>">
                            </div>
                            <div class="form-group">
                                <label for="name">Name of the Category</label>
                                <input id="name" class="form-control" type="text" name="name" placeholder="Enter the name of the Category here" value="<?= $category_data['name'] ?>">
                            </div>
                            <div class="form-group">
                                <label for="my-input">Category image</label><br>
                                <img src="<?= base_url('public/uploads/categories/' . $category_data['icon']) ?>" width="20%" alt="category image">
                                <input type="file" name="icon" id="icon" accept="image/*">
                            </div>
                            <div class="form-group">
                                <label for="commision">Commision for the Category</label>
                                <input id="commision" class="form-control" type="number" name="commision" placeholder="Enter the commission for the Category here" value="<?= $category_data['admin_commission'] ?>">
                            </div>
                            <div class="custom-control custom-switch mt-4">
                                <?php if ($category_data['admin_commission'] == 1) : ?>
                                    <input class="custom-control-input" type="checkbox" name="changer" id="changer" checked="true" aria-checked="true">
                                    <label class="custom-control-label" for="changer">
                                        <p id="para" class="ml-10">
                                            Make Disable Category
                                        </p>
                                    </label>
                                <?php else : ?>
                                    <input class="custom-control-input" type="checkbox" name="changer" id="changer">
                                    <label class="custom-control-label" for="changer">
                                        <p id="para" class="ml-10">
                                            Make Enable Category
                                        </p>
                                    </label>
                                <?php endif; ?>
                            </div>
                            <div class="creation-cat">
                                <div class="row my-3">
                                    <div class="col-lg-1"></div>
                                    <div class="col-lg-4">
                                        <button type="submit" class="btn btn-primary btn-lg w-100">
                                            Update Category
                                        </button>
                                    </div>
                                    <div class="col-lg-2"></div>
                                    <div class="col-lg-4 flex-end">
                                        <button type="reset" class="btn btn-danger btn-lg w-100">
                                            Reset data
                                        </button>
                                    </div>
                                    <div class="col-lg-1"></div>
                                </div>

                            </div>
                            <?= form_close(); ?>
                        </div>
                    </div>
                </div>
            </div>
    </section>
</div>