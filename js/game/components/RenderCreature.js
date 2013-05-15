function RenderCreature (parent) {
	
	this.render = function (img, width, height, opposite) {
		var holder = parent.create('div');
		var sprite = holder.create('img');
		sprite.setAttribute('src', img);
		sprite.setAttribute('width', width);
		sprite.setAttribute('height', height);
		holder.style.width = width + 'px';
		holder.style.height = height + 'px';
		if (opposite){
			holder.opposite = true;
			holder.style.WebkitTransform = 'scaleX(-1)';
		}
		else {
			holder.opposite = false;
		}
		return holder;
	};
}