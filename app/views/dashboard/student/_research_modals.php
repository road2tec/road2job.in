<!-- Research item modal -->
<div class="modal fade" id="researchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="" enctype="multipart/form-data" data-guard-submit>
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Research Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label" for="research-modals-type">Type</label>
                            <select id="research-modals-type" name="type" class="form-select" required>
                                <option value="research-paper">Research Paper</option>
                                <option value="project">Research Project</option>
                                <option value="publication">Publication</option>
                                <option value="conference-paper">Conference Paper</option>
                                <option value="patent">Patent</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="research-modals-publication-date">Publication / completion date</label>
                            <input id="research-modals-publication-date" type="date" name="publication_date" class="form-control">
                        </div>
                    </div>
                    <div class="mb-2"><label class="form-label" for="research-modals-title">Title</label><input id="research-modals-title" type="text" name="title" class="form-control" required></div>
                    <div class="mb-2"><label class="form-label" for="research-modals-authors-collaborators">Authors / collaborators</label><input id="research-modals-authors-collaborators" type="text" name="authors_collaborators" class="form-control" placeholder="e.g. Jane Doe, Prof. John Smith"></div>
                    <div class="mb-2"><label class="form-label" for="research-modals-description">Description / abstract</label><textarea id="research-modals-description" name="description" class="form-control" rows="4"></textarea></div>
                    <div class="mb-2"><label class="form-label" for="research-modals-external-reference">External reference (URL, DOI, patent number)</label><input id="research-modals-external-reference" type="text" name="external_reference" class="form-control"></div>
                    <div class="mb-2"><label class="form-label" for="research-modals-attachment">Attachment (optional, PDF/image)</label><input id="research-modals-attachment" type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
