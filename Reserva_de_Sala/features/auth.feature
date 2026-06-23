@auth @modulo-autenticacion
Feature: Autenticación y Control de Acceso
  Como visitante, administrador o coordinador
  Quiero iniciar y cerrar sesión en el sistema
  Para acceder a las funcionalidades según mi rol

  Background:
    Given el sistema tiene un usuario registrado con email "admin@example.com", contraseña "Password123" y rol "admin"
    And el sistema tiene un usuario registrado con email "coordinador@example.com", contraseña "Password123" y rol "coordinador"

  @auth @F-AUTH-001
  Scenario: Inicio de sesión exitoso con credenciales válidas
    Given un visitante no autenticado
    When el visitante inicia sesión con email "admin@example.com" y contraseña "Password123"
    Then el sistema autentica al usuario
    And el sistema devuelve un token JWT válido
    And el sistema permite acceder a rutas protegidas

  @auth @F-AUTH-002
  Scenario: Inicio de sesión con email no registrado
    Given un visitante no autenticado
    When el visitante inicia sesión con email "no-existe@example.com" y contraseña "Password123"
    Then el sistema rechaza la autenticación
    And el sistema muestra un error "Credenciales inválidas"
    And el sistema no devuelve un token JWT

  @auth @F-AUTH-003
  Scenario: Inicio de sesión con contraseña incorrecta
    Given un visitante no autenticado
    When el visitante inicia sesión con email "admin@example.com" y contraseña "WrongPassword"
    Then el sistema rechaza la autenticación
    And el sistema muestra un error "Credenciales inválidas"
    And el sistema no devuelve un token JWT

  @auth @F-AUTH-004
  Scenario: Acceso a ruta protegida sin token JWT
    Given un visitante no autenticado
    When el visitante intenta acceder al recurso "GET /api/sucursales"
    Then el sistema rechaza la petición con código 401
    And el sistema muestra un error "Token no proporcionado"

  @auth @F-AUTH-005
  Scenario: Acceso a ruta protegida con token JWT expirado o inválido
    Given un visitante no autenticado
    When el visitante intenta acceder al recurso "GET /api/sucursales" con un token inválido
    Then el sistema rechaza la petición con código 401
    And el sistema muestra un error "Token inválido o expirado"

  @auth @coordinador @F-AUTH-006
  Scenario: Acceso a módulo restringido con rol insuficiente
    Given un coordinador autenticado con email "coordinador@example.com"
    When el coordinador intenta acceder al módulo "usuarios"
    Then el sistema rechaza la petición con código 403
    And el sistema muestra un error "No tienes permiso para esta acción"

  @auth @F-AUTH-007
  Scenario: Cierre de sesión exitoso
    Given un administrador autenticado con email "admin@example.com"
    When el administrador cierra sesión
    Then el token JWT queda invalidado
    And el sistema ya no permite acceder a rutas protegidas con ese token
