alter table carreras modify  nombre varchar(255);
alter table talleres modify  nombre varchar(255);

alter table matriculas add tipo_persona char(2);
alter table matriculas add carrera char(20), add foreign key fk_carrera(carrera) references carreras(codigo) on delete no action;



update matriculas M
left join personas__matricula PM on M.persona_matricula = PM.identificacion
set M.carrera = PM.carrera, M.tipo_persona = PM.tipo_Persona;



alter table oferta_cocurriculares
drop foreign key oferta_cocurriculares_taller_foreign;



alter table oferta_cocurriculares
add CONSTRAINT `oferta_cocurriculares_taller_foreign` FOREIGN KEY (`taller`) REFERENCES `talleres` (`codigo`);

