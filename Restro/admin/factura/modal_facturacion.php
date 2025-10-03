<div class="modal fade" id="modalFacturar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="factura/generar_factura.php" id="formFactura" target="_blank">
                <div class="modal-header bg-gradient-primary">
                    <h5 class="modal-title text-white">💳 Facturar Mesa #<span id="modalMesaNumero"></span></h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="mesa_id" id="modalMesaId">
                    <input type="hidden" name="comprobante_tipo" id="comprobanteTipo" value="B02">

                    <!-- Tipo de factura -->
                    <div class="form-group">
                        <label>Tipo de Factura *</label>
                        <select class="form-control" name="tipo_factura" id="tipoFactura" required>
                            <option value="Final" data-comprobante="B02">Factura consumidor Final (B02)</option>
                            <option value="Fiscal" data-comprobante="B01">Factura con Crédito Fiscal (B01)</option>
                            <option value="Credito" data-comprobante="">Crédito</option>
                        </select>
                    </div>

                    <!-- Cliente -->
                    <div class="form-group" id="clienteGroup" style="display: none;">
                        <label>Seleccionar Cliente *</label>
                        <select class="form-control" name="cliente_id" id="clienteSelect">
                            <option value="">Seleccione un cliente</option>
                            <?php
                            $clientes_result->data_seek(0);
                            while ($cliente = $clientes_result->fetch_object()): ?>
                                <option value="<?= $cliente->customer_id ?>">
                                    <?= $cliente->customer_name . ' - ' . $cliente->rnc_cedula ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Método de pago -->
                    <div class="form-group">
                        <label>Método de Pago *</label>
                        <div class="btn-group btn-group-toggle w-100" data-toggle="buttons" id="metodoPagoGroup">
                            <label class="btn btn-outline-primary metodo-pago-btn">
                                <input type="radio" name="metodo_pago" value="Efectivo" required>
                                <i class="fas fa-money-bill-wave mr-2"></i>Efectivo
                            </label>
                            <label class="btn btn-outline-primary metodo-pago-btn">
                                <input type="radio" name="metodo_pago" value="Tarjeta Débito">
                                <i class="fas fa-credit-card mr-2"></i>Débito
                            </label>
                            <label class="btn btn-outline-primary metodo-pago-btn">
                                <input type="radio" name="metodo_pago" value="Tarjeta Crédito">
                                <i class="fas fa-credit-card mr-2"></i>Crédito
                            </label>
                            <label class="btn btn-outline-primary metodo-pago-btn">
                                <input type="radio" name="metodo_pago" value="Transferencia">
                                <i class="fas fa-exchange-alt mr-2"></i>Transferencia
                            </label>
                        </div>
                    </div>

                    <!-- Monto recibido -->
                    <div class="form-group" id="montoEfectivoGroup" style="display: none;">
                        <label>Monto Recibido *</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">RD$</span>
                            </div>
                            <input type="number" step="0.01" class="form-control" name="monto_recibido" id="montoRecibido" placeholder="0.00" min="0">
                        </div>
                        <small class="form-text text-muted">Ingrese el monto que recibió del cliente</small>
                    </div>

                    <!-- Vuelto -->
                    <div class="alert alert-warning" id="vueltoSection" style="display: none;">
                        <h6 class="alert-heading">💵 Vuelto a Entregar</h6>
                        <h3 class="text-center mb-0">RD$ <span id="vueltoMonto">0.00</span></h3>
                    </div>

                    <!-- Resumen factura -->
                    <div class="alert alert-info resumen-factura">
                        <h6 class="alert-heading">🧾 Resumen de Factura</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1">Subtotal:
                                    <span class="float-right">RD$ <span id="modalSubtotal">0.00</span></span>
                                </p>
                                <p class="mb-1">ITEBIS (<?= $config->itebis_porcentaje ?>%):
                                    <span class="float-right">RD$ <span id="modalItebis">0.00</span></span>
                                </p>
                                <p class="mb-1">Servicio (<?= $config->servicio_porcentaje ?>%):
                                    <span class="float-right">RD$ <span id="modalServicio">0.00</span></span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-gradient-primary p-3 rounded text-center">
                                    <h5 class="text-white mb-0">TOTAL A PAGAR</h5>
                                    <h2 class="text-white font-weight-bold mb-0">RD$ <span id="modalTotal">0.00</span></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btnGenerarFactura" class="btn btn-success">Generar Factura</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Mostrar campo cliente según factura
        $('#tipoFactura').change(function() {
            let comprobante = $(this).find(':selected').data('comprobante') || '';
            $('#comprobanteTipo').val(comprobante);

            if ($(this).val() === 'Final') {
                $('#clienteGroup').hide();
                $('#clienteSelect').removeAttr('required');
            } else {
                $('#clienteGroup').show();
                $('#clienteSelect').attr('required', 'required');
            }
        });

        // Mostrar monto recibido si pago en efectivo
        $('input[name="metodo_pago"]').change(function() {
            if ($(this).val() === 'Efectivo') {
                $('#montoEfectivoGroup').show();
                $('#montoRecibido').attr('required', 'required');
            } else {
                $('#montoEfectivoGroup').hide();
                $('#montoRecibido').removeAttr('required').val('');
                $('#vueltoSection').hide();
            }
        });

        // Calcular vuelto dinámico
        $('#montoRecibido').on('input', function() {
            var montoRecibido = parseFloat($(this).val()) || 0;
            var total = parseFloat($('#modalTotal').text());

            if (montoRecibido >= total) {
                $('#vueltoMonto').text((montoRecibido - total).toFixed(2));
                $('#vueltoSection').show();
            } else {
                $('#vueltoSection').hide();
            }
        });

        // Validación y envío del formulario
        $('#formFactura').on('submit', function(e) {
            e.preventDefault();
            console.log('Formulario enviado - Previniendo comportamiento por defecto');

            var metodoPago = $('input[name="metodo_pago"]:checked').val();
            var montoRecibido = parseFloat($('#montoRecibido').val()) || 0;
            var total = parseFloat($('#modalTotal').text());
            var tipoFactura = $('#tipoFactura').val();
            var clienteId = $('#clienteSelect').val();

            // Validaciones
            if ((tipoFactura === 'Fiscal' || tipoFactura === 'Credito') && !clienteId) {
                alert('❌ Debe seleccionar un cliente para factura ' + tipoFactura);
                return false;
            }

            if (metodoPago === 'Efectivo' && montoRecibido < total) {
                alert('❌ El monto recibido es menor al total.');
                return false;
            }

            // Deshabilitar botón para evitar múltiples clics
            $('#btnGenerarFactura').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

            // Crear formulario temporal para abrir en nueva pestaña
            var tempForm = document.createElement('form');
            tempForm.action = $(this).attr('action');
            tempForm.method = 'POST';
            tempForm.target = '_blank'; // Esto es crucial para abrir en nueva pestaña
            tempForm.style.display = 'none';

            // Agregar todos los campos del formulario original
            $(this).find(':input').each(function() {
                if (this.name) {
                    // Para radio buttons y checkboxes, solo agregar si están seleccionados
                    if ((this.type === 'radio' || this.type === 'checkbox') && !this.checked) {
                        return true; // continuar con el siguiente
                    }

                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = this.name;
                    input.value = $(this).val();
                    tempForm.appendChild(input);
                }
            });

            // Agregar el formulario al documento y enviarlo
            document.body.appendChild(tempForm);
            tempForm.submit();
            console.log('Formulario enviado a nueva pestaña');

            // Cerrar el modal inmediatamente
            $('#modalFacturar').modal('hide');

            // Mostrar mensaje de éxito
            mostrarMensajeExito();

            // Restaurar botón después de 2 segundos
            setTimeout(function() {
                $('#btnGenerarFactura').prop('disabled', false).html('Generar Factura');
            }, 2000);

            return false;
        });

        // Función para mostrar mensaje de éxito
        function mostrarMensajeExito() {
            // Crear mensaje de éxito con botón de aceptar
            var alerta = $(
                '<div class="alert alert-success alert-dismissible fade show floating-alert" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 350px;">' +
                '<div class="d-flex align-items-center">' +
                '<span class="alert-icon" style="font-size: 24px; margin-right: 15px;"><i class="ni ni-like-2"></i></span>' +
                '<div class="flex-grow-1">' +
                '<h4 class="alert-heading mb-1">¡Factura Generada!</h4>' +
                '<p class="mb-0">La factura se ha generado correctamente y se abrió en una nueva pestaña.</p>' +
                '</div>' +
                '</div>' +
                '<div class="text-right mt-3">' +
                '<button type="button" class="btn btn-sm btn-outline-success" onclick="$(this).closest(\'.alert\').alert(\'close\')">Aceptar</button>' +
                '</div>' +
                '</div>'
            );

            // Agregar al cuerpo del documento
            $('body').append(alerta);

            // Auto-ocultar después de 8 segundos
            setTimeout(function() {
                alerta.alert('close');
            }, 8000);
        }

        // Limpiar el modal cuando se cierre
        $('#modalFacturar').on('hidden.bs.modal', function() {
            // Resetear selecciones
            $('#metodoPagoGroup .btn').removeClass('active');
            $('#montoRecibido').val('');
            $('#vueltoSection').hide();
            $('#tipoFactura').val('Final');
            $('#clienteGroup').hide();
            $('#clienteSelect').removeAttr('required');
            $('#montoEfectivoGroup').hide();
            $('#montoRecibido').removeAttr('required');
        });
    });
</script>