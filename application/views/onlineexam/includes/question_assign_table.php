<table class="table table-bordered">
    <thead>
        <tr>
            <th><?= translate('question') ?></th>
            <th><?= translate('action') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($questions) > 0):
            foreach ($questions as $q): ?>
                <tr>
                    <td><?= strip_tags($q['question']); ?></td>
                    <td>
                        <button type="button" class="btn btn-default btn-circle"
                            onclick="assignQuestionToBranches(<?= $q['id'] ?>)">
                            <i class="fas fa-plus"></i> <?= translate('assign') ?>
                        </button>
                    </td>
                </tr>
            <?php endforeach; else: ?>
            <tr>
                <td colspan="2"><?= translate('no_data_available') ?></td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>