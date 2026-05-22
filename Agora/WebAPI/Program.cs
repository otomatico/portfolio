/*
* Ejecutar : #>dotnet run --project .\WebAPI\WebAPI.csproj
* dotnet run --project .\WebAPI\WebAPI.csproj --environment "Development" --configuration Debug
* dotnet run --project .\WebAPI\WebAPI.csproj --environment "Staging"
*/

using System.Data;
using MySql.Data.MySqlClient;
using System.Data.SqlClient;
using Agora.BLL;
using Agora.DAL.Data;
using Microsoft.AspNetCore.Authentication;
using Microsoft.AspNetCore.Authentication.JwtBearer;
using Microsoft.Extensions.Configuration;
using Microsoft.Identity.Web;

//var builder = WebApplication.CreateBuilder(args);
var builder = WebApplication.CreateBuilder(
    new WebApplicationOptions
    {
        Args = args,
        ApplicationName = typeof(Program).Assembly.FullName,
        ContentRootPath = Directory.GetCurrentDirectory(),
        //EnvironmentName = Environments.Development,
        //EnvironmentName = Environments.Staging,
        //EnvironmentName = Environments.Production,
        WebRootPath = "client-app/Views"
    }
);

#if DEBUG
Console.WriteLine($"Environment Name: {builder.Environment.EnvironmentName}");
Console.WriteLine($"ContentRoot Path: {builder.Environment.ContentRootPath}");
Console.WriteLine($"WebRootPath: {builder.Environment.WebRootPath}");
#endif

if (builder.Environment.EnvironmentName == Environments.Development)
{
    // MySql -> MariaDB 10.*
    builder.Services.AddScoped<IDbConnection, MySqlConnection>();
}
else
{
    //SQLServer 17.*
    builder.Services.AddScoped<IDbConnection, SqlConnection>();
}
//Add Dependency
builder.Services.AddScoped<IMasterData, MasterData>();
builder.Services.AddScoped<IMasterManager, MasterManager>();
builder.Services.AddScoped<ITemplateData, TemplateData>();
builder.Services.AddScoped<ITemplateFieldData, TemplateFieldData>();
builder.Services.AddScoped<ITemplateAllowedChildrenData, TemplateAllowedChildrenData>();
builder.Services.AddScoped<ITemplateManager, TemplateManager>();
builder.Services.AddScoped<INodeData, NodeData>();
builder.Services.AddScoped<INodeRelationData, NodeRelationData>();
builder.Services.AddScoped<INodeManager, NodeManager>();

/*
builder.Services.AddTransient<IOperationTransient, Operation>();
builder.Services.AddScoped<IOperationScoped, Operation>();
builder.Services.AddSingleton<IOperationSingleton, Operation>();
*/


// Add services to the container.
builder.Services.AddAuthentication(JwtBearerDefaults.AuthenticationScheme)
    .AddMicrosoftIdentityWebApi(builder.Configuration.GetSection("AzureAd"));

builder.Services.AddControllers();

// Learn more about configuring Swagger/OpenAPI at https://aka.ms/aspnetcore/swashbuckle
builder.Services.AddEndpointsApiExplorer();
builder.Services.AddSwaggerGen();
//Reglas de CORS
builder.Services.AddCors(p => p.AddPolicy("corsapp", builder =>
{
    builder.WithOrigins("*").AllowAnyMethod().AllowAnyHeader();
}));

var app = builder.Build();

// Configure the HTTP request pipeline.
if (app.Environment.IsDevelopment())
{
    app.UseSwagger();
    app.UseSwaggerUI();
}
//Activa CORS
app.UseHttpsRedirection();
app.UseRouting();
app.UseCors("corsapp");

//Activa Archivos Estaticos
app.UseStaticFiles();

// app.UseMyMiddleware();


app.UseAuthentication();
app.UseAuthorization();
//app.UseEndpoints(endpoints =>
//{
//    endpoints.MapControllers();
//});
app.MapControllers();

app.Run();

/*
public static class MyMiddlewareExtensions
{
    public static IApplicationBuilder UseMyMiddleware(this IApplicationBuilder builder)
    {
        return builder.UseMiddleware<MyMiddleware>();
    }
}
*/
