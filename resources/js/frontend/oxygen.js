const oxygen = {
    addComponent(data, componentId, source, designSet) {
        angular.element(document.getElementById('ct-controller-ui')).scope().iframeScope.addComponentFromSource(data, componentId, source, designSet);
    }
};

export default oxygen;