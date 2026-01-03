<?php

return [

    //ESTANDARES DE MENSAJERIA

    // Operaciones generales
    'M1'  => 'El registro se guardó correctamente.',
    'M2'  => 'El registro se actualizó correctamente.',
    'M3'  => 'El registro fue eliminado correctamente.',
    'M4'  => '¿Está seguro de que desea eliminar este registro? Esta acción no eliminará la información de forma permanente.',
    'M5'  => 'La operación fue cancelada.',

    // Cédula/RUC
    'M6'  => 'Ingrese la cédula o RUC antes de continuar.',
    'M7'  => 'La cédula o RUC ingresada no cumple con la longitud requerida para continuar.',
    'M8'  => 'La cédula o RUC debe contener solamente números.',
    'M9'  => 'La cédula o RUC ingresada no es válida.',
    'M10' => 'Ya existe un registro con la misma cédula o RUC.',

    //CAMPOS OBLIGATORIOS
    'M11' => 'Existen campos obligatorios sin completar. Verifique e intente nuevamente.',
    // RUC NO EXISTE
    'M12' => 'El RUC ingresado está incompleto.',
    'M13' => 'El RUC debe contener únicamente números.',
    'M14' => 'El RUC ingresado no es válido.',
    'M15' => 'Ya existe un registro con el mismo RUC.',

    // Nombre / Apellido
    'M16' => 'Ingrese el nombre para continuar.',
    'M17' => 'Ingrese el apellido para continuar.',

    // Teléfono
    'M18' => 'Ingrese el número telefónico.',
    'M19' => 'El número telefónico está incompleto.',
    'M20' => 'El número telefónico debe contener solo números.',
    'M21' => 'El número telefónico ingresado no es válido.',

    // Correo
    'M22' => 'Ingrese el correo electrónico.',
    'M23' => 'El correo electrónico ingresado no tiene un formato válido.',
    'M24' => 'Ya existe un registro con el mismo correo electrónico.',

    // Producto
    'M25' => 'Ingrese el nombre del producto.',
    'M26' => 'El producto ya existe en el sistema.',
    'M27' => 'El producto seleccionado no se encuentra activo.',
    'M28' => 'El producto seleccionado no está disponible.',

    // Precio
    'M29' => 'Ingrese el precio del producto.',
    'M30' => 'El precio debe ser un valor numérico.',
    'M31' => 'El precio no puede ser un valor negativo.',

    // Stock / Cantidad
    'M32' => 'No existe stock suficiente para el producto seleccionado.',
    'M33' => 'Ingrese la cantidad del producto.',
    'M34' => 'La cantidad debe ser un valor numérico.',
    'M35' => 'La cantidad ingresada no es válida.',
    'M36' => 'La cantidad ingresada supera el stock disponible.',

    // Proveedor / Orden de compra
    'M37' => 'Seleccione un proveedor para continuar.',
    'M38' => 'La orden de compra no contiene productos.',
    'M39' => 'La orden de compra se registró correctamente.',
    'M40' => 'La orden de compra fue anulada correctamente.',

    // Recepción
    'M41' => 'Seleccione una orden de compra válida.',
    'M42' => 'La cantidad recibida no es válida.',
    'M43' => 'La recepción se registró correctamente.',

    // Cliente / Factura
    'M44' => 'Seleccione un cliente para continuar.',
    'M45' => 'La factura no contiene productos.',
    'M46' => 'La factura se generó correctamente.',
    'M47' => 'La factura fue anulada correctamente.',
    'M48' => 'La factura no puede modificarse en su estado actual.',

    // Carrito
    'M49' => 'El carrito de compras está vacío.',
    'M50' => 'El producto fue agregado al carrito correctamente.',
    'M51' => 'El producto fue retirado del carrito correctamente.',
    'M52' => 'El carrito se actualizó correctamente.',

    // Pago
    'M53' => 'Seleccione un método de pago para continuar.',
    'M54' => 'La compra se realizó correctamente.',

    // Fecha
    'M55' => 'La fecha ingresada no es válida.',
    'M56' => 'La fecha seleccionada no puede ser posterior a la fecha actual.',

    // Búsqueda
    'M57' => 'Ingrese un criterio para realizar la búsqueda.',
    'M58' => 'El criterio ingresado no es válido.',
    'M59' => 'No se encontraron resultados con los criterios ingresados.',

    // Operación
    'M60' => 'La operación no se puede realizar en el estado actual.',


   //EXCEPCIONES

    'E1'  => 'Falla de conexión a la base de datos.',
    'E2'  => 'Tiempo de espera excedido en la base de datos.',
    'E3'  => 'Error en la ejecución de consultas SQL.',
    'E4'  => 'Violación de clave primaria.',
    'E5'  => 'Violación de clave foránea.',
    'E6'  => 'Violación de restricciones de integridad.',
    'E7'  => 'Error en el manejo de transacciones.',
    'E8'  => 'Error de concurrencia.',
    'E9'  => 'Inconsistencia en el estado del proceso.',
    'E10' => 'Error de conversión de datos.',
    'E11' => 'Recurso del sistema no disponible.',
    'E12' => 'Error inesperado del sistema.',
    'E13' => 'Error en la generación de documentos.',
    'E14' => 'Error en la actualización de inventario.',
    'E15' => 'Error en la validación de reglas de negocio.',
    'E16' => 'Error de sesión inválida.',
    'E17' => 'Error en el procesamiento de pagos.',
    'E18' => 'Error de acceso a datos.',
    'E19' => 'Error en la carga de configuraciones.',
    'E20' => 'Error en la sincronización de procesos.',
    'gen.error' => 'Ocurrió un error inesperado.',

    // Aliases del módulo productos (compatibilidad con tu controller viejo)
    'productos.crear.ok'        => 'El registro se guardó correctamente.',   // M1
    'productos.actualizar.ok'   => 'El registro se actualizó correctamente.',// M2
    'productos.eliminar.ok'     => 'El registro fue eliminado correctamente.',// M3
    'productos.no.disponible'   => 'La operación no se puede realizar en el estado actual.', // M60
    'productos.duplicado'       => 'El producto ya existe en el sistema.',   // M26
    'productos.sin.registros'   => 'No se encontraron resultados con los criterios ingresados.', // M59
    'productos.sin.coincidencias'=> 'No se encontraron resultados con los criterios ingresados.', // M59
    'productos.producto.vacio'  => 'Ingrese el nombre del producto.',        // M25
    'productos.precio.vacio'    => 'Ingrese el precio del producto.',        // M29
    'productos.precio.invalido' => 'El precio debe ser un valor numérico.',  // M30
    'productos.precio.negativo' => 'El precio no puede ser un valor negativo.', // M31
    'productos.stock.invalido'  => 'La cantidad ingresada no es válida.',    // M35
    'productos.parametro.vacio' => 'Ingrese un criterio para realizar la búsqueda.', // M57
    'productos.parametro.invalido' => 'El criterio ingresado no es válido.', // M58
];

