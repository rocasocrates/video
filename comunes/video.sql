drop table if exists provincias cascade;

create table provincias (
    id     int         constraint pk_provincias primary key,
    nombre varchar(50) not null
);

insert into provincias (id, nombre)
values ( 0, 'Desconocida'),
       ( 1, 'Araba'),
       ( 2, 'Albacete'),
       ( 3, 'Alacant'),
       ( 4, 'Almería'),
       ( 5, 'Ávila'),
       ( 6, 'Badajoz'),
       ( 7, 'Balears'),
       ( 8, 'Barcelona'),
       ( 9, 'Burgos'),
       (10, 'Cáceres'),
       (11, 'Cádiz');

drop table if exists poblaciones cascade;

create table poblaciones (
    id           bigserial    constraint pk_poblaciones primary key,
    nombre       varchar(100) not null,
    provincia_id int          default 0 not null
                              constraint fk_poblaciones_provincias
                              references provincias (id)
                              on delete set default on update cascade
);

create index idx_poblaciones_provincia_id on poblaciones (provincia_id);

insert into poblaciones (nombre, provincia_id)
values ('Sanlúcar de Barrameda', 11),
       ('Chipiona', 11);

drop table if exists socios cascade;

create table socios (
    id           bigserial     constraint pk_socios primary key,
    numero       numeric(13)   not null constraint uq_socios_numero unique,
    dni          char(9),
    nombre       varchar(100)  not null,
    direccion    varchar(150),
    poblacion_id bigint        constraint fk_socios_poblaciones
                               references poblaciones (id)
                               on delete set null on update cascade,
    codpostal    char(5),
    telefono     varchar(15),
    password     char(32)
);

create index idx_socios_dni on socios (dni);
create index idx_socios_poblacion_id on socios (poblacion_id);
create index idx_socios_codpostal on socios (codpostal);

insert into socios (numero, dni, nombre, direccion, poblacion_id,
                    codpostal, password)
values (1000, '11111111A', 'Juan Rodríguez', 'C/ Falsa, 123', 1,
        '11540', md5('juan')),
       (1001, '22222222B', 'María González', 'C/ Ancha, 25', 2,
        '11550', md5('maria'));

drop function if exists formato(cadena text) cascade;

create function formato(cadena text) returns text as $$
begin
    return translate(upper(cadena), 'ÁÉÍÓÚ', 'AEIOU');
end;
$$ language plpgsql;

drop view if exists v_socios cascade;

create view v_socios as
select s.*,
       p.nombre as poblacion_nombre,
       pr.nombre as provincia_nombre
  from socios s left join poblaciones p on poblacion_id = p.id
  join provincias pr on provincia_id = pr.id;

drop table if exists peliculas cascade;

create table peliculas (
    id         bigserial    constraint pk_peliculas primary key,
    titulo     varchar(50)  not null,
    precio_alq numeric(4,2) not null constraint peliculas_precio_alq_positivo
                            check (precio_alq >= 0)
);

create index idx_peliculas_precio_alq on peliculas (precio_alq);

insert into peliculas (titulo, precio_alq)
values ('Batman Begins', 1.50),
       ('El caballero oscuro', 2.00),
       ('La leyenda renace', 3.00);

drop table if exists copias cascade;

create table copias (
    id          bigserial   constraint pk_copias primary key,
    codigo      numeric(13) not null constraint uq_copias_codigo unique,
    pelicula_id bigint      not null constraint fk_copias_peliculas
                            references peliculas (id) on delete cascade
                            on update cascade,
    fecha_alta  date        default current_date,
    borrada     bool        not null default false
);

create index idx_copias_pelicula_id on copias (pelicula_id);
create index idx_copias_fecha_alta on copias (fecha_alta);
create index idx_copias_borrada on copias (borrada);

insert into copias (codigo, pelicula_id)
values (100, 1),
       (101, 2),
       (102, 2),
       (103, 2),
       (104, 3),
       (105, 3);

drop view if exists v_copias cascade;

create view v_copias as
select c.id, codigo, titulo, precio_alq, fecha_alta, pelicula_id,
       trim(to_char(precio_alq, '99D99 L')) as precio_alq_format,
       to_char(fecha_alta, 'DD-MM-YYYY') as fecha_alta_format
  from peliculas p join copias c on pelicula_id = p.id
 where not borrada;

drop table if exists alquileres cascade;

create table alquileres (
    id        bigserial constraint pk_alquileres primary key,
    copia_id  bigint    not null constraint fk_alquileres_copias
                        references copias (id) on delete no action
                        on update cascade,
    socio_id  bigint    not null constraint fk_alquileres_socios
                        references socios (id) on delete no action
                        on update cascade,
    fecha_alq date      not null default current_date,
    fecha_dev date,
    constraint ck_fechas_validas
        check (fecha_dev is null or fecha_alq <= fecha_dev)
);

create index idx_alquileres_copia_id on alquileres (copia_id);
create index idx_alquileres_socio_id on alquileres (socio_id);
create index idx_alquileres_fecha_alq on alquileres (fecha_alq);

insert into alquileres (copia_id, socio_id, fecha_alq, fecha_dev)
values (1, 1, current_date - 4, current_date - 3),
       (1, 2, current_date, null),
       (2, 1, current_date - 1, null),
       (3, 1, current_date - 1, current_date),
       (4, 1, current_date, null);

drop view if exists pendientes cascade;

create view pendientes as
select a.*, codigo, titulo, precio_alq, fecha_alta, pelicula_id,
       precio_alq_format, fecha_alta_format
  from alquileres a join v_copias v on copia_id = v.id
 where fecha_dev is null;

