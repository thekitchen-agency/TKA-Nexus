(function() {
  function initNexusField(container) {
    if (container.dataset.nexusInitialized) return;
    container.dataset.nexusInitialized = "true";

    const typeTabs = container.querySelectorAll('.nexus-type-tab');
    const typeInputs = container.querySelectorAll('.nexus-type-content');
    const typeHidden = container.querySelector('.nexus-type-value');
    const toggleDetailsBtn = container.querySelector('.nexus-toggle-details-btn');
    const detailsPanel = container.querySelector('.nexus-details-panel');
    const iconItems = container.querySelectorAll('.nexus-icon-item');
    const iconHidden = container.querySelector('.nexus-icon-value');
    const clearIconBtn = container.querySelector('.nexus-clear-icon-btn');
    const iconNameDisplay = container.querySelector('.nexus-icon-name-display');

    // Type tab switching
    typeTabs.forEach(tab => {
      tab.addEventListener('click', (e) => {
        e.preventDefault();
        const selectedType = tab.dataset.type;

        typeTabs.forEach(t => t.classList.toggle('is-active', t === tab));
        typeInputs.forEach(panel => {
          panel.style.display = panel.dataset.type === selectedType ? 'block' : 'none';
        });

        if (typeHidden) {
          typeHidden.value = selectedType;
        }
      });
    });

    // Details panel toggle
    if (toggleDetailsBtn && detailsPanel) {
      toggleDetailsBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const isHidden = detailsPanel.classList.toggle('is-hidden');
        toggleDetailsBtn.classList.toggle('is-open', !isHidden);
        toggleDetailsBtn.setAttribute('aria-expanded', !isHidden);
      });
    }

    // Icon picker selection
    if (iconItems.length && iconHidden) {
      iconItems.forEach(item => {
        item.addEventListener('click', (e) => {
          e.preventDefault();
          const iconName = item.dataset.icon;
          const isSelected = item.classList.contains('is-selected');

          iconItems.forEach(i => i.classList.remove('is-selected'));

          if (isSelected) {
            iconHidden.value = '';
            if (iconNameDisplay) iconNameDisplay.textContent = '';
          } else {
            item.classList.add('is-selected');
            iconHidden.value = iconName;
            if (iconNameDisplay) iconNameDisplay.textContent = iconName;
          }
        });
      });
    }

    // Clear icon button
    if (clearIconBtn && iconHidden) {
      clearIconBtn.addEventListener('click', (e) => {
        e.preventDefault();
        iconItems.forEach(i => i.classList.remove('is-selected'));
        iconHidden.value = '';
        if (iconNameDisplay) iconNameDisplay.textContent = '';
      });
    }
  }

  function initAll() {
    document.querySelectorAll('.nexus-field-container').forEach(initNexusField);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }

  if (window.Craft && window.Craft.initUiElements) {
    Craft.initUiElements(initAll);
  }
})();
