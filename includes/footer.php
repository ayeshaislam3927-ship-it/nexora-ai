<?php
/**
 * NEXORA - Main Site Footer Component
 */
?>
    <script src="/assets/js/app.js"></script>
    <?php if (isset($extraJs)): ?>
        <?php foreach ($extraJs as $js): ?>
            <script src="<?= htmlspecialchars($js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
