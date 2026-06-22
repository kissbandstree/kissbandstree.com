(() => {
  let selectedGroup = null;
  let selectedMember = null;

  const samePair = (group, member) => selectedGroup === group && selectedMember === member;

  const getTextTargets = (group, member) => {
    const selectors = [
      `.stats-member[data-group="${group}"][data-member="${member}"]`,
    ];

    if (group === 'gender') {
      selectors.push(`.stats-gender[data-member="${member}"]`);
    }
    if (group === 'band-photos') {
      selectors.push(`.stats-band-photo[data-member="${member}"]`);
    }
    if (group === 'artist-photos') {
      selectors.push(`.stats-artist-photo[data-member="${member}"]`);
    }
    if (group === 'instruments') {
      selectors.push(`.stats-instr[data-member="${member}"]`);
    }

    return document.querySelectorAll(selectors.join(','));
  };

  const getSliceTargets = (group, member) => {
    return document.querySelectorAll(`.cake-pie path[data-group="${group}"][data-member="${member}"]`);
  };

  const setHighlight = (group, member, enabled) => {
    getTextTargets(group, member).forEach((node) => {
      node.classList.toggle('stats-highlight', enabled);
    });

    getSliceTargets(group, member).forEach((node) => {
      node.classList.toggle('stats-slice-highlight', enabled);
    });
  };

  const clearSelectedHighlight = () => {
    if (selectedGroup !== null && selectedMember !== null) {
      setHighlight(selectedGroup, selectedMember, false);
    }

    selectedGroup = null;
    selectedMember = null;
  };

  const enterPair = (group, member) => {
    if (selectedGroup !== null && selectedMember !== null && !samePair(group, member)) {
      clearSelectedHighlight();
    }

    setHighlight(group, member, true);
  };

  const leavePair = (group, member) => {
    if (!samePair(group, member)) {
      setHighlight(group, member, false);
    }
  };

  const toggleSelectedHighlight = (group, member) => {
    const alreadySelected = samePair(group, member);
    clearSelectedHighlight();

    if (!alreadySelected) {
      selectedGroup = group;
      selectedMember = member;
      setHighlight(group, member, true);
    }
  };

  const bindPairEvents = (node, group, member, options = {}) => {
    const enter = () => enterPair(group, member);
    const leave = () => leavePair(group, member);

    node.addEventListener('mouseenter', enter);
    node.addEventListener('mouseover', enter);
    node.addEventListener('pointerenter', enter);
    node.addEventListener('mouseleave', leave);
    node.addEventListener('mouseout', leave);
    node.addEventListener('pointerleave', leave);
    node.addEventListener('focus', () => enterPair(group, member));
    node.addEventListener('blur', () => leavePair(group, member));

    node.addEventListener('click', (event) => {
      if (options.preventClickDefault) {
        event.preventDefault();
      }
      toggleSelectedHighlight(group, member);
    });

    node.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' || event.key === ' ') {
        if (options.preventClickDefault) {
          event.preventDefault();
        }
        toggleSelectedHighlight(group, member);
      }
    });
  };

  document.querySelectorAll('.cake-pie path[data-group][data-member]').forEach((node) => {
    bindPairEvents(node, node.dataset.group, node.dataset.member, { preventClickDefault: true });
  });

  document.querySelectorAll('.stats-member[data-group][data-member]').forEach((node) => {
    bindPairEvents(node, node.dataset.group, node.dataset.member);
  });

  document.querySelectorAll('.stats-band-photo[data-member]').forEach((node) => {
    bindPairEvents(node, 'band-photos', node.dataset.member, { preventClickDefault: true });
  });

  document.querySelectorAll('.stats-artist-photo[data-member]').forEach((node) => {
    bindPairEvents(node, 'artist-photos', node.dataset.member, { preventClickDefault: true });
  });

  document.querySelectorAll('.stats-instr[data-member]').forEach((node) => {
    bindPairEvents(node, 'instruments', node.dataset.member, { preventClickDefault: true });
  });

  document.querySelectorAll('.stats-gender[data-member]').forEach((node) => {
    bindPairEvents(node, 'gender', node.dataset.member, { preventClickDefault: true });
  });

})();