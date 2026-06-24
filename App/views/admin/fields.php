<?php
// App/views/admin/fields.php
?>
<div class="row">
    <div class="col-12 mb-3">
        <div class="alert alert-info bg-info bg-opacity-10 border-0 text-white">
            <strong>Form Manager:</strong> Add new fields to the Vehicle Registration form or Owner Profile form, and remove or restore fields from the active form without losing configuration.
        </div>
    </div>

    <!-- Left Column: Add Field Form -->
    <div class="col-lg-4 mb-4">
        <div class="card glass-panel border-0 p-4">
            <h5 class="text-white mb-4" id="formTitle"><i class="fa-solid fa-plus text-success me-2"></i>Add Form Field</h5>
            <div id="editFieldNotice" class="alert alert-info bg-info bg-opacity-15 border-0 text-white small d-none">
                <strong>Editing field:</strong> <span id="editingFieldName"></span>
            </div>
            
            <?php if (isset($error) && $error): ?>
                <div class="alert alert-danger bg-danger bg-opacity-25 border-0 text-white"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success bg-success bg-opacity-25 border-0 text-white"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/admin/fields" id="dynamicFieldForm">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="form_action" id="form_action" value="create">
                <input type="hidden" name="field_id" id="field_id" value="">

                <div class="mb-3">
                    <label for="entity" class="form-label text-secondary">Target Entity</label>
                    <select class="form-select" id="entity" name="entity">
                        <option value="vehicle">Vehicle Registration Form</option>
                        <option value="owner">Owner Profile Form</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="field_name" class="form-label text-secondary">Field Label / Name</label>
                    <input type="text" class="form-control" id="field_name" name="field_name" placeholder="e.g. RFID Chip Code" required>
                </div>

                <div class="mb-3">
                    <label for="field_type" class="form-label text-secondary">Field Input Type</label>
                    <select class="form-select" id="field_type" name="field_type">
                        <option value="text">Single Line Text</option>
                        <option value="number">Numeric Value</option>
                        <option value="dropdown">Dropdown Selection</option>
                        <option value="date">Date picker</option>
                        <option value="checkbox">Checkbox Option</option>
                        <option value="radio">Radio Buttons</option>
                        <option value="textarea">Multi-line Text Area</option>
                        <option value="file">Document / File Attachment</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="options" class="form-label text-secondary">Options (comma-separated)</label>
                    <textarea class="form-control" id="options" name="options" placeholder="e.g. Yes, No, Unknown (only for dropdown/radio)"></textarea>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_required" name="is_required" value="1">
                    <label class="form-check-label text-secondary" for="is_required">Required Field</label>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100" id="dynamicFormSubmit">
                        <i class="fa-solid fa-square-plus me-1"></i> Add Form Field
                    </button>
                    <button type="button" class="btn btn-outline-light" id="resetFieldForm">
                        <i class="fa-solid fa-rotate-left me-1"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Column: List Existing Fields -->
    <div class="col-lg-8">
        <div class="card glass-panel border-0 p-4 mb-4">
            <h5 class="text-white mb-3"><i class="fa-solid fa-car text-success me-2"></i>Vehicle Form Custom Fields</h5>
            <div class="table-responsive">
                <table class="table text-secondary">
                    <thead>
                        <tr>
                            <th>Field Name</th>
                            <th>Type</th>
                            <th>Required</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($vehicleFields)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No custom vehicle fields defined yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($vehicleFields as $field): ?>
                                <?php $fieldActive = isset($field['is_active']) ? (bool)$field['is_active'] : true; ?>
                                <tr>
                                    <td class="text-white fw-bold"><?= htmlspecialchars($field['field_name']) ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($field['field_type']) ?></span></td>
                                    <td><?= $field['is_required'] ? '<span class="badge bg-danger">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                                    <td><?= $fieldActive ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-warning text-dark">Removed</span>' ?></td>
                                    <td>
                                        <button type="button" class="btn btn-info btn-sm me-1 edit-field-btn" data-field-id="<?= $field['id'] ?>" data-entity="<?= htmlspecialchars($field['entity']) ?>" data-field-name="<?= htmlspecialchars($field['field_name']) ?>" data-field-type="<?= htmlspecialchars($field['field_type']) ?>" data-options="<?= htmlspecialchars($field['options']) ?>" data-is-required="<?= $field['is_required'] ?>" title="Edit field">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </button>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/fields" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="field_id" value="<?= $field['id'] ?>">
                                            <input type="hidden" name="active" value="<?= $fieldActive ? '0' : '1' ?>">
                                            <button type="submit" class="btn btn-<?= $fieldActive ? 'warning' : 'success' ?> btn-sm me-1" title="<?= $fieldActive ? 'Remove from forms' : 'Restore to forms' ?>">
                                                <i class="fa-solid fa-<?= $fieldActive ? 'ban' : 'rotate-right' ?>"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/fields" class="d-inline" onsubmit="return confirm('Permanently delete this field from the system? This cannot be undone.');">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="field_id" value="<?= $field['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Delete field permanently"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card glass-panel border-0 p-4">
            <h5 class="text-white mb-3"><i class="fa-solid fa-users text-success me-2"></i>Owner Profile Custom Fields</h5>
            <div class="table-responsive">
                <table class="table text-secondary">
                    <thead>
                        <tr>
                            <th>Field Name</th>
                            <th>Type</th>
                            <th>Required</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ownerFields)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No custom owner fields defined yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ownerFields as $field): ?>
                                <?php $fieldActive = isset($field['is_active']) ? (bool)$field['is_active'] : true; ?>
                                <tr>
                                    <td class="text-white fw-bold"><?= htmlspecialchars($field['field_name']) ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($field['field_type']) ?></span></td>
                                    <td><?= $field['is_required'] ? '<span class="badge bg-danger">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                                    <td><?= $fieldActive ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-warning text-dark">Removed</span>' ?></td>
                                    <td>
                                        <button type="button" class="btn btn-info btn-sm me-1 edit-field-btn" data-field-id="<?= $field['id'] ?>" data-entity="<?= htmlspecialchars($field['entity']) ?>" data-field-name="<?= htmlspecialchars($field['field_name']) ?>" data-field-type="<?= htmlspecialchars($field['field_type']) ?>" data-options="<?= htmlspecialchars($field['options']) ?>" data-is-required="<?= $field['is_required'] ?>" title="Edit field">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </button>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/fields" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="field_id" value="<?= $field['id'] ?>">
                                            <input type="hidden" name="active" value="<?= $fieldActive ? '0' : '1' ?>">
                                            <button type="submit" class="btn btn-<?= $fieldActive ? 'warning' : 'success' ?> btn-sm me-1" title="<?= $fieldActive ? 'Remove from forms' : 'Restore to forms' ?>">
                                                <i class="fa-solid fa-<?= $fieldActive ? 'ban' : 'rotate-right' ?>"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/fields" class="d-inline" onsubmit="return confirm('Permanently delete this field from the system? This cannot be undone.');">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="field_id" value="<?= $field['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Delete field permanently"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('dynamicFieldForm');
        const formActionInput = document.getElementById('form_action');
        const fieldIdInput = document.getElementById('field_id');
        const fieldNameInput = document.getElementById('field_name');
        const entitySelect = document.getElementById('entity');
        const fieldTypeSelect = document.getElementById('field_type');
        const optionsInput = document.getElementById('options');
        const isRequiredInput = document.getElementById('is_required');
        const submitButton = document.getElementById('dynamicFormSubmit');
        const resetButton = document.getElementById('resetFieldForm');
        const formTitle = document.querySelector('.card.glass-panel h5');
        const editFieldNotice = document.getElementById('editFieldNotice');
        const editingFieldName = document.getElementById('editingFieldName');

        function resetForm() {
            formActionInput.value = 'create';
            fieldIdInput.value = '';
            fieldNameInput.value = '';
            entitySelect.value = 'vehicle';
            fieldTypeSelect.value = 'text';
            optionsInput.value = '';
            isRequiredInput.checked = false;
            submitButton.innerHTML = '<i class="fa-solid fa-square-plus me-1"></i> Add Form Field';
            formTitle.innerHTML = '<i class="fa-solid fa-plus text-success me-2"></i>Add Form Field';
            editFieldNotice.classList.add('d-none');
            editingFieldName.textContent = '';
        }

        resetButton.addEventListener('click', resetForm);

        document.querySelectorAll('.edit-field-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                formActionInput.value = 'update';
                fieldIdInput.value = this.dataset.fieldId;
                entitySelect.value = this.dataset.entity;
                fieldNameInput.value = this.dataset.fieldName;
                fieldTypeSelect.value = this.dataset.fieldType;
                optionsInput.value = this.dataset.options;
                isRequiredInput.checked = this.dataset.isRequired === '1' || this.dataset.isRequired === 'true';
                submitButton.innerHTML = '<i class="fa-solid fa-save me-1"></i> Update Field';
                formTitle.innerHTML = '<i class="fa-solid fa-pen-to-square text-success me-2"></i>Edit Form Field';
                editFieldNotice.classList.remove('d-none');
                editingFieldName.textContent = this.dataset.fieldName || 'selected field';
                window.scrollTo({ top: form.offsetTop - 120, behavior: 'smooth' });
            });
        });
    });
</script>
