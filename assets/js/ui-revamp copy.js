document.addEventListener('DOMContentLoaded', ()=>{
  const menu = window.revampMenu || [],
        grid = document.getElementById('menuGrid'),
        crumbs = document.getElementById('breadcrumbTrail');
  let stack = [], current = menu;

  function render() {
    grid.innerHTML = '';
    current.forEach((i,idx)=>{
      const hasKids = i.children?.length,
            icon = i.icon || 'icon-folder',
            onclick = hasKids ? `onclick="go(${idx})"`:'';
      grid.innerHTML += `
        <div class="dashboard-card common-card" ${onclick}>
          <div class="card-icon"><i class="icons ${icon}"></i></div>
          <h4>${i.label}</h4>
          ${!hasKids && i.url?`<a class="card-link" href="${baseURL+i.url}"></a>`:''}
        </div>`;
    });
    renderBreadcrumb();
  }

  function renderBreadcrumb() {
    crumbs.innerHTML = `<a href="javascript:reset()" class="bread-home"><i class="fas fa-home"></i></a>`;
    stack.forEach((i,idx)=>{
      const itm= stack[idx];
      crumbs.innerHTML += `<span class="sep">></span>
        <a href="javascript:jump(${idx})">${itm.label}</a>`;
    });
  }

  window.go = idx => {
    stack.push(current[idx]);
    current = current[idx].children;
    render();
    history.pushState({stack},'', '');
  };

  window.jump = idx => {
    stack = stack.slice(0,idx+1);
    current = stack[idx].children;
    render();
    history.pushState({stack},'', '');
  };

  window.reset = () => {
    stack = [];
    current = menu;
    render();
    history.pushState({stack},'', '');
  };

  window.addEventListener('popstate', e=>{
    stack = e.state?.stack||[];
    current = stack.length? stack[stack.length-1].children: menu;
    render();
  });

  history.replaceState({stack:[]},'', '');
  render();
});
