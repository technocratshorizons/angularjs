/* SystemJS module definition */
declare var module: NodeModule;
interface NodeModule {
  id: string;
}

interface JQuery {
   addClass(className: string): JQuery;
   attr(attributeName: string, value: string|number): JQuery;
}
interface JQuery {
     selectpicker(options?: any, callback?: Function):any;
     DataTable(options?: any, callback?: Function):any;
}
